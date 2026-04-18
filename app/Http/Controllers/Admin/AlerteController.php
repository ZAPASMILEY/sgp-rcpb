<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AlerteMail;
use App\Mail\AlerteVipMail;
use App\Models\Alerte;
use App\Models\Entite;
use App\Models\LoginFailure;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;

class AlerteController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'toutes');

        // --- Alertes personnalisées ---
        $alertesQuery = Alerte::with('createur')->latest();
        $alertesPersonnalisees = $alertesQuery->get();

        // --- Alertes de sécurité (login failures) ---
        $loginFailures = LoginFailure::latest('attempted_at')->get();

        // --- Statistiques ---
        $totalPersonnalisees = $alertesPersonnalisees->count();
        $totalSecurite = $loginFailures->count();
        $totalActives = $alertesPersonnalisees->where('statut', 'active')->count();
        $totalCritiques = $alertesPersonnalisees->where('priorite', 'critique')->count();
        $totalResolues = $alertesPersonnalisees->where('statut', 'resolue')->count();
        $tentativesAujourdhui = LoginFailure::whereDate('attempted_at', today())->count();

        // --- Données combinées pour l'onglet "Toutes" ---
        $combined = collect();

        foreach ($alertesPersonnalisees as $alerte) {
            $combined->push([
                'id'         => $alerte->id,
                'type'       => 'personnalisee',
                'priorite'   => $alerte->priorite,
                'titre'      => $alerte->titre,
                'message'    => $alerte->message,
                'statut'     => $alerte->statut,
                'ip_address' => $alerte->ip_address,
                'auteur'     => $alerte->createur?->name ?? '-',
                'date'       => $alerte->created_at,
            ]);
        }

        foreach ($loginFailures as $failure) {
            $combined->push([
                'id'         => $failure->id,
                'type'       => 'securite',
                'priorite'   => 'haute',
                'titre'      => 'Tentative de connexion échouée',
                'message'    => 'Email: ' . ($failure->email ?? 'inconnu'),
                'statut'     => 'active',
                'ip_address' => $failure->ip_address,
                'auteur'     => $failure->email ?? '-',
                'date'       => $failure->attempted_at,
            ]);
        }

        $combined = $combined->sortByDesc('date')->values();

        // --- Filtrage par onglet ---
        $items = match ($tab) {
            'securite'       => $combined->where('type', 'securite')->values(),
            'personnalisees' => $combined->where('type', 'personnalisee')->values(),
            'critiques'      => $combined->filter(fn ($a) => $a['priorite'] === 'critique' || $a['priorite'] === 'haute')->values(),
            default          => $combined,
        };

        $counts = [
            'toutes'         => $combined->count(),
            'securite'       => $combined->where('type', 'securite')->count(),
            'personnalisees' => $combined->where('type', 'personnalisee')->count(),
            'critiques'      => $combined->filter(fn ($a) => $a['priorite'] === 'critique' || $a['priorite'] === 'haute')->count(),
        ];

        // --- Chart: alertes 7 derniers jours ---
        $chartCategories = [];
        $chartSecurite = [];
        $chartPersonnalisees = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartCategories[] = $day->translatedFormat('D d');
            $chartSecurite[] = LoginFailure::whereDate('attempted_at', $day->toDateString())->count();
            $chartPersonnalisees[] = Alerte::whereDate('created_at', $day->toDateString())->count();
        }

        return view('admin.alertes.index', compact(
            'items',
            'tab',
            'counts',
            'totalPersonnalisees',
            'totalSecurite',
            'totalActives',
            'totalCritiques',
            'totalResolues',
            'tentativesAujourdhui',
            'chartCategories',
            'chartSecurite',
            'chartPersonnalisees',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre'    => ['required', 'string', 'max:255'],
            'message'  => ['nullable', 'string', 'max:2000'],
            'type'     => ['required', 'in:personnalisee,securite'],
            'priorite' => ['required', 'in:basse,moyenne,haute,critique'],
            'diffuser_email' => ['nullable', 'in:1'],
        ]);

        $diffuserEmail = $request->boolean('diffuser_email');

        $alerte = Alerte::create([
            'titre'      => $validated['titre'],
            'message'    => $validated['message'] ?? null,
            'type'       => $validated['type'],
            'priorite'   => $validated['priorite'],
            'statut'     => 'active',
            'ip_address' => $request->ip(),
            'created_by' => auth()->id(),
        ]);

        // Diffuser l'alerte à tous les utilisateurs (notifications in-app)
        $alerte->diffuserATous();

        // Diffuser par email si demandé
        if ($diffuserEmail) {
            $alerte->load('createur');

            // Identifier les 3 dirigeants (PCA, DG, DGA) via l'entité faîtière
            $entite = Entite::first();
            $vipEmails = [];
            $vipRecipients = [];

            if ($entite) {
                if ($entite->pca_email) {
                    $vipEmails[] = strtolower($entite->pca_email);
                    $vipRecipients[] = [
                        'email' => $entite->pca_email,
                        'name'  => trim($entite->pca_prenom . ' ' . $entite->pca_nom),
                        'role'  => 'Président du Conseil d\'Administration',
                    ];
                }
                if ($entite->directrice_generale_email) {
                    $vipEmails[] = strtolower($entite->directrice_generale_email);
                    $vipRecipients[] = [
                        'email' => $entite->directrice_generale_email,
                        'name'  => trim($entite->directrice_generale_prenom . ' ' . $entite->directrice_generale_nom),
                        'role'  => 'Directeur(trice) Général(e)',
                    ];
                }
                if ($entite->dga_email) {
                    $vipEmails[] = strtolower($entite->dga_email);
                    $vipRecipients[] = [
                        'email' => $entite->dga_email,
                        'name'  => trim($entite->dga_prenom . ' ' . $entite->dga_nom),
                        'role'  => 'Directeur(trice) Général(e) Adjoint(e)',
                    ];
                }
            }

            // Envoyer le template VIP aux 3 dirigeants
            foreach ($vipRecipients as $vip) {
                Mail::to($vip['email'])->queue(new AlerteVipMail($alerte, $vip['name'], $vip['role']));
            }

            // Envoyer le template standard aux autres utilisateurs
            $users = User::whereNotNull('email')->get();
            foreach ($users as $user) {
                if (!in_array(strtolower($user->email), $vipEmails)) {
                    Mail::to($user->email)->queue(new AlerteMail($alerte, $user->name));
                }
            }
        }

        $message = 'Alerte créée avec succès.';
        if ($diffuserEmail) {
            $message .= ' Email envoyé à tous les utilisateurs.';
        }

        return redirect()->route('admin.alertes.index', ['tab' => 'personnalisees'])
            ->with('status', $message);
    }

    public function updateStatut(Request $request, Alerte $alerte): RedirectResponse
    {
        $request->validate([
            'statut' => ['required', 'in:active,resolue,ignoree'],
        ]);

        $alerte->update(['statut' => $request->statut]);

        return redirect()->route('admin.alertes.index')
            ->with('status', 'Statut mis à jour.');
    }

    public function destroy(Alerte $alerte): RedirectResponse
    {
        $alerte->delete();

        return redirect()->route('admin.alertes.index')
            ->with('status', 'Alerte supprimée.');
    }

    public function destroyAll(): RedirectResponse
    {
        Alerte::query()->delete();

        return redirect()->route('admin.alertes.index')
            ->with('status', 'Toutes les alertes ont été supprimées.');
    }

    public function lireTout(Request $request): RedirectResponse
    {
        $request->user()->alertesNonLues()->update(['lu' => true, 'lu_at' => now()]);

        return back()->with('status', 'Toutes les notifications ont été marquées comme lues.');
    }
}
