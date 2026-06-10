<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasFormations;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Contrôleur unifié "Mes formations" — remplace les 7 contrôleurs par rôle.
 *
 * Rôles couverts : PCA, DG, DGA, Assistante_Dg, Conseillers_Dg,
 *                  Directeur_Direction, Directeur_Caisse, Directeur_Technique,
 *                  Chef_Service, Chef_Agence, Chef_Guichet, et tout le Personnel.
 *
 * Le dispatch s'effectue via Auth::user()->role dans les trois méthodes
 * du trait HasFormations.
 *
 * create() / store() permettent à n'importe quel agent de soumettre
 * sa propre formation pour validation RH.
 */
class FormationController extends Controller
{
    use HasFormations;

    // ══════════════════════════════════════════════════════════════════════════
    // Implémentation des abstraits du trait HasFormations
    // ══════════════════════════════════════════════════════════════════════════

    protected function getAgentIds(Request $request): array
    {
        // "Mes formations" = uniquement les formations de l'utilisateur connecté,
        // quel que soit son rôle. Les formations de l'équipe passent par le module RH.
        $agentId = Auth::user()?->agent_id;

        return $agentId ? [$agentId] : [];
    }

    protected function getLayoutName(): string
    {
        return match (Auth::user()?->role) {
            'PCA'                                                                => 'layouts.pca',
            'DG'                                                                 => 'layouts.dg',
            'DGA'                                                                => 'layouts.dga',
            'Assistante_Dg', 'Conseillers_Dg'                                   => 'layouts.subordonne',
            'Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'    => 'layouts.directeur',
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                       => 'layouts.chef',
            default                                                              => 'layouts.personnel',
        };
    }

    protected function getPdfRoutePrefix(): string
    {
        return match (Auth::user()?->role) {
            'PCA'                                                                => 'pca',
            'DG'                                                                 => 'dg',
            'DGA'                                                                => 'dga',
            'Assistante_Dg', 'Conseillers_Dg'                                   => 'subordonne',
            'Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'    => 'directeur',
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                       => 'chef',
            default                                                              => 'personnel',
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Points d'entrée publics — lecture
    // ══════════════════════════════════════════════════════════════════════════

    public function __invoke(Request $request)
    {
        return $this->mesFormations($request);
    }

    public function pdf(Request $request, Formation $formation)
    {
        return $this->formationPdf($request, $formation);
    }

    /**
     * Supprime une formation en attente soumise par l'agent lui-même.
     * Interdit si la formation est déjà validée ou refusée.
     */
    public function destroy(Formation $formation): RedirectResponse
    {
        $agent = Auth::user()?->agent;

        if (! $agent || $formation->agent_id !== $agent->id) {
            abort(403, 'Vous ne pouvez supprimer que vos propres formations.');
        }

        if ($formation->statut !== 'en_attente') {
            return back()->with('error', 'Seules les formations en attente peuvent être supprimées.');
        }

        // Supprimer l'attestation associée
        if ($formation->attestation_path) {
            Storage::disk('public')->delete($formation->attestation_path);
        }

        $formation->delete();

        $indexRoute = $this->getPdfRoutePrefix() . '.formations.index';
        return redirect()->route($indexRoute)
            ->with('status', 'Formation supprimée.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Soumission d'une formation par l'agent lui-même
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Formulaire de soumission d'une formation par l'agent connecté.
     * Accessible à tous les rôles (route 'formation.soumettre').
     */
    public function create(Request $request): View
    {
        $agent        = Auth::user()?->agent;
        $anneeEnCours = Annee::currentOpen();
        $layout       = $this->getLayoutName();
        $domaines     = Formation::DOMAINES;
        $types        = Formation::TYPES;
        $themesExistants = Formation::distinct()->orderBy('theme')->pluck('theme');

        return view('formations.soumettre', compact(
            'agent', 'anneeEnCours', 'layout', 'domaines', 'types', 'themesExistants'
        ));
    }

    /**
     * Enregistre la formation soumise par l'agent.
     * Statut : en_attente — le RH doit valider avant qu'elle apparaisse.
     */
    public function store(Request $request): RedirectResponse
    {
        $user  = Auth::user();
        $agent = $user?->agent;

        if (! $agent) {
            return back()->with('error', 'Aucun agent lié à votre compte.');
        }

        $anneeEnCours = Annee::currentOpen();
        $annee        = $anneeEnCours?->annee ?? now()->year;

        $validated = $request->validate([
            'theme'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'string', 'in:' . implode(',', array_keys(Formation::TYPES))],
            'domaine'      => ['required', 'string', 'in:' . implode(',', array_keys(Formation::DOMAINES))],
            'date_debut'   => ['required', 'date', 'before_or_equal:today', function ($_, $value, $fail) use ($annee) {
                if ((int) date('Y', strtotime($value)) !== $annee) {
                    $fail("La date de début doit appartenir à l'année {$annee}.");
                }
            }],
            'date_fin'     => ['required', 'date', 'after_or_equal:date_debut', 'before_or_equal:today', function ($_, $value, $fail) use ($annee) {
                if ((int) date('Y', strtotime($value)) !== $annee) {
                    $fail("La date de fin doit appartenir à l'année {$annee}.");
                }
            }],
            'duree_heures' => ['required', 'integer', 'min:1', 'max:9999'],
            'attestation'  => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'date_debut.before_or_equal' => 'La date de début ne peut pas être dans le futur.',
            'date_fin.before_or_equal'   => 'La date de fin ne peut pas être dans le futur.',
            'date_fin.after_or_equal'    => 'La date de fin doit être égale ou postérieure à la date de début.',
            'attestation.required'       => 'L\'attestation est obligatoire.',
            'attestation.mimes'          => 'L\'attestation doit être un PDF ou une image (JPG, PNG, WEBP).',
            'attestation.max'            => 'L\'attestation ne doit pas dépasser 5 Mo.',
        ]);

        $path = $request->file('attestation')->store('attestations', 'public');

        Formation::create([
            'agent_id'         => $agent->id,
            'theme'            => $validated['theme'],
            'type'             => $validated['type'],
            'domaine'          => $validated['domaine'],
            'date_debut'       => $validated['date_debut'],
            'date_fin'         => $validated['date_fin'],
            'duree_heures'     => $validated['duree_heures'],
            'attestation_path' => $path,
            'statut'           => 'en_attente',
            'created_by'       => $user->id,
        ]);

        // Notifier les RH
        $rhUsers = User::where('role', 'RH')->get();
        foreach ($rhUsers as $rh) {
            Alerte::notifier(
                $rh->id,
                'Nouvelle formation à valider',
                trim($agent->prenom . ' ' . $agent->nom) . ' a soumis une formation « ' . $validated['theme'] . ' » en attente de validation.',
                'moyenne',
                route('rh.formations.index')
            );
        }

        $indexRoute = $this->getPdfRoutePrefix() . '.formations.index';
        return redirect()->route($indexRoute)
            ->with('status', 'Formation soumise avec succès. En attente de validation RH.');
    }

}

