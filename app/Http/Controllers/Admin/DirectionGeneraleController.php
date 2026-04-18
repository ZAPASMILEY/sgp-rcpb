<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DirectionGeneraleController extends Controller
{
    public function index(): View
    {
        $entite = Entite::latest()->first();
        $direction = $entite
            ? Direction::where('entite_id', $entite->id)->where('nom', 'Direction Générale')->first()
            : null;

        $membres     = collect();
        $secretaires = collect();
        $conseillers = collect();

        if ($entite) {
            $membres = User::whereIn('role', ['DG', 'Assistante_Dg', 'DGA'])
                ->where('pca_entite_id', $entite->id)
                ->get();

            $secretaires = User::where('role', 'Secretaire_assistante')
                ->where('pca_entite_id', $entite->id)
                ->get();

            $conseillers = User::where('role', 'Conseillers_Dg')
                ->where('pca_entite_id', $entite->id)
                ->get();
        }

        return view('admin.direction-generale.index', [
            'entite'     => $entite,
            'direction'  => $direction,
            'membres'    => $membres,
            'secretaires'=> $secretaires,
            'conseillers'=> $conseillers,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();

        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $dejaConfiguree = Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->exists();

        return view('admin.direction-generale.create', [
            'entite'          => $entite,
            'dejaConfiguree'  => $dejaConfiguree,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();

        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $validated = $request->validate([
            // DG
            'directrice_generale_prenom'                    => ['required', 'string', 'max:255'],
            'directrice_generale_nom'                       => ['required', 'string', 'max:255'],
            'directrice_generale_email'                     => ['required', 'email', Rule::unique('users', 'email')],
            'directrice_generale_sexe'                      => ['required', 'in:Homme,Femme,Autres'],
            'directrice_generale_date_prise_fonction'       => ['required', 'date_format:Y-m'],
            'directrice_generale_photo'                     => ['nullable', 'image', 'max:2048'],
            // DGA
            'dga_prenom'                                    => ['required', 'string', 'max:255'],
            'dga_nom'                                       => ['required', 'string', 'max:255'],
            'dga_email'                                     => ['required', 'email', Rule::unique('users', 'email')],
            'dga_sexe'                                      => ['required', 'in:Homme,Femme,Autres'],
            'dga_date_prise_fonction'                       => ['required', 'date_format:Y-m'],
            'dga_photo'                                     => ['nullable', 'image', 'max:2048'],
            // Assistante DG
            'assistante_dg_prenom'                          => ['required', 'string', 'max:255'],
            'assistante_dg_nom'                             => ['required', 'string', 'max:255'],
            'assistante_dg_email'                           => ['required', 'email', Rule::unique('users', 'email')],
            'assistante_dg_sexe'                            => ['required', 'in:Homme,Femme,Autres'],
            'assistante_dg_date_prise_fonction'             => ['required', 'date_format:Y-m'],
        ]);

        // Stockage des photos
        foreach (['directrice_generale_photo' => 'directrice_generale_photo_path', 'dga_photo' => 'dga_photo_path'] as $input => $column) {
            if ($request->hasFile($input)) {
                $validated[$column] = $request->file($input)->store('entites', 'public');
            }
            unset($validated[$input]);
        }

        DB::transaction(function () use ($entite, $validated) {
            // Mettre à jour l'entite avec les infos DG, DGA, Assistante
            $entite->update([
                'directrice_generale_prenom'             => $validated['directrice_generale_prenom'],
                'directrice_generale_nom'                => $validated['directrice_generale_nom'],
                'directrice_generale_email'              => $validated['directrice_generale_email'],
                'directrice_generale_photo_path'         => $validated['directrice_generale_photo_path'] ?? $entite->directrice_generale_photo_path,
                'directrice_generale_sexe'               => $validated['directrice_generale_sexe'],
                'directrice_generale_date_prise_fonction'=> $validated['directrice_generale_date_prise_fonction'],
                'dga_prenom'                             => $validated['dga_prenom'],
                'dga_nom'                                => $validated['dga_nom'],
                'dga_email'                              => $validated['dga_email'],
                'dga_photo_path'                         => $validated['dga_photo_path'] ?? $entite->dga_photo_path,
                'dga_sexe'                               => $validated['dga_sexe'],
                'dga_date_prise_fonction'                => $validated['dga_date_prise_fonction'],
                'assistante_dg_prenom'                   => $validated['assistante_dg_prenom'],
                'assistante_dg_nom'                      => $validated['assistante_dg_nom'],
                'assistante_dg_email'                    => $validated['assistante_dg_email'],
                'assistante_dg_sexe'                     => $validated['assistante_dg_sexe'],
                'assistante_dg_date_prise_fonction'      => $validated['assistante_dg_date_prise_fonction'],
            ]);

            // Créer les 3 comptes utilisateurs liés à l'entite
            $personnels = [
                [
                    'name'                => $validated['directrice_generale_prenom'].' '.$validated['directrice_generale_nom'],
                    'email'               => $validated['directrice_generale_email'],
                    'role'                => 'DG',
                    'sexe'                => $validated['directrice_generale_sexe'],
                    'date_prise_fonction' => $validated['directrice_generale_date_prise_fonction'],
                ],
                [
                    'name'                => $validated['dga_prenom'].' '.$validated['dga_nom'],
                    'email'               => $validated['dga_email'],
                    'role'                => 'DGA',
                    'sexe'                => $validated['dga_sexe'],
                    'date_prise_fonction' => $validated['dga_date_prise_fonction'],
                ],
                [
                    'name'                => $validated['assistante_dg_prenom'].' '.$validated['assistante_dg_nom'],
                    'email'               => $validated['assistante_dg_email'],
                    'role'                => 'Assistante_Dg',
                    'sexe'                => $validated['assistante_dg_sexe'],
                    'date_prise_fonction' => $validated['assistante_dg_date_prise_fonction'],
                ],
            ];

            foreach ($personnels as $p) {
                $password = Str::random(12);
                User::create([
                    'name'                => $p['name'],
                    'email'               => $p['email'],
                    'password'            => Hash::make($password),
                    'role'                => $p['role'],
                    'pca_entite_id'       => $entite->id,
                    'sexe'                => $p['sexe'],
                    'date_prise_fonction' => $p['date_prise_fonction'],
                ]);
                Mail::to($p['email'])->send(new WelcomeMail(
                    $p['name'], $p['email'], $password, $p['role'], url('/login')
                ));
            }

            // Créer la Direction Générale (si pas encore créée)
            $dgUser = User::where('email', $validated['directrice_generale_email'])->first();
            Direction::firstOrCreate(
                ['entite_id' => $entite->id, 'nom' => 'Direction Générale'],
                [
                    'user_id'           => $dgUser?->id,
                    'directeur_prenom'  => $validated['directrice_generale_prenom'],
                    'directeur_nom'     => $validated['directrice_generale_nom'],
                    'directeur_email'   => $validated['directrice_generale_email'],
                ]
            );
        });

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Direction Generale configuree avec succes.');
    }

    public function createSecretaire(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        return view('admin.direction-generale.create-secretaire', ['entite' => $entite]);
    }

    public function storeSecretaire(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create');
        }

        $validated = $request->validate([
            'prenom'               => ['required', 'string', 'max:255'],
            'nom'                  => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', Rule::unique('users', 'email')],
            'sexe'                 => ['required', 'in:Homme,Femme,Autres'],
            'date_prise_fonction'  => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $password = Str::random(12);
        User::create([
            'name'                => $validated['prenom'].' '.$validated['nom'],
            'email'               => $validated['email'],
            'password'            => Hash::make($password),
            'role'                => 'Secretaire_assistante',
            'pca_entite_id'       => $entite->id,
            'sexe'                => $validated['sexe'],
            'date_prise_fonction' => $validated['date_prise_fonction'],
        ]);

        Mail::to($validated['email'])->send(new WelcomeMail(
            $validated['prenom'].' '.$validated['nom'],
            $validated['email'],
            $password,
            'Secretaire_assistante',
            url('/login')
        ));

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Secrétaire ajouté avec succès. Les identifiants ont été envoyés par email.');
    }

    public function destroySecretaire(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Secrétaire supprimé.');
    }

    public function createConseiller(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        return view('admin.direction-generale.create-conseiller', ['entite' => $entite]);
    }

    public function storeConseiller(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create');
        }

        $validated = $request->validate([
            'prenom'               => ['required', 'string', 'max:255'],
            'nom'                  => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', Rule::unique('users', 'email')],
            'sexe'                 => ['required', 'in:Homme,Femme,Autres'],
            'date_prise_fonction'  => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'specialite'           => ['nullable', 'string', 'max:255'],
        ]);

        $password = Str::random(12);
        User::create([
            'name'                => $validated['prenom'].' '.$validated['nom'],
            'email'               => $validated['email'],
            'password'            => Hash::make($password),
            'role'                => 'Conseillers_Dg',
            'pca_entite_id'       => $entite->id,
            'sexe'                => $validated['sexe'],
            'date_prise_fonction' => $validated['date_prise_fonction'],
        ]);

        Mail::to($validated['email'])->send(new WelcomeMail(
            $validated['prenom'].' '.$validated['nom'],
            $validated['email'],
            $password,
            'Conseillers_Dg',
            url('/login')
        ));

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Conseiller ajouté avec succès. Les identifiants ont été envoyés par email.');
    }

    public function destroyConseiller(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Conseiller supprimé.');
    }
}
