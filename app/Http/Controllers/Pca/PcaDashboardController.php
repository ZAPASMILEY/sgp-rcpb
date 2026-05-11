<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PcaDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // ── Entité faîtière (identité institutionnelle uniquement) ──────────
        $entite = Entite::with(['dg', 'dga', 'dgaSecretaire', 'pca', 'assistante'])
            ->where('pca_agent_id', $request->user()->agent_id)
            ->firstOrFail();

        // ── Année de pilotage ──────────────────────────────────────────────
        $annee = (int) $request->query('annee', now()->year);

        // ── Utilisateur DG ─────────────────────────────────────────────────
        $dgUser   = $entite->dg_agent_id
            ? User::where('agent_id', $entite->dg_agent_id)->first()
            : null;
        $dgUserId = $dgUser?->id ?? 0;

        $dgNom      = $entite->dg ? trim($entite->dg->prenom . ' ' . $entite->dg->nom) : '';
        $dgInitiale = strtoupper(substr($dgNom ?: 'D', 0, 1));

        // ── Fiches d'objectifs — DG uniquement ───────────────────────────
        $fichesBase = FicheObjectif::query()
            ->where('assignable_type', \App\Models\User::class)
            ->where('assignable_id', $dgUserId)
            ->whereYear('date', $annee);

        $totalFiches     = (clone $fichesBase)->count();
        $fichesAcceptees = (clone $fichesBase)->where('statut', 'acceptee')->count();
        $fichesEnAttente = (clone $fichesBase)->where('statut', 'en_attente')->count();
        $fichesRefusees  = (clone $fichesBase)->where('statut', 'refusee')->count();
        $tauxAvancement  = round((clone $fichesBase)->avg('avancement_percentage') ?? 0, 1);

        // ── Évaluations — DG uniquement ───────────────────────────────────
        $evalsBase = Evaluation::query()
            ->where('evaluable_type', \App\Models\User::class)
            ->where('evaluable_id', $dgUserId)
            ->whereYear('date_debut', $annee);

        $evalsTotal     = (clone $evalsBase)->count();
        $evalsValidees  = (clone $evalsBase)->where('statut', 'valide')->count();
        $evalsSoumises  = (clone $evalsBase)->where('statut', 'soumis')->count();
        $evalsRefusees  = (clone $evalsBase)->where('statut', 'refuse')->count();
        $evalsBrouillon = (clone $evalsBase)->where('statut', 'brouillon')->count();
        $noteMoyenne    = round((clone $evalsBase)->where('statut', 'valide')->avg('note_finale') ?? 0, 2);

        // ── Fiches DG récentes ────────────────────────────────────────────
        $fichesDGRecentes = $dgUserId
            ? FicheObjectif::where('assignable_type', \App\Models\User::class)
                ->where('assignable_id', $dgUserId)
                ->whereYear('date', $annee)
                ->latest('date')
                ->take(6)
                ->get()
            : collect();

        // ── Personnel du cabinet ───────────────────────────────────────────
        $personnelCabinet = collect([
            ['role' => 'Directeur(trice) Général(e)', 'agent' => $entite->dg,           'icon' => 'fas fa-user-tie',    'color' => 'bg-emerald-100 text-emerald-700'],
            ['role' => 'DGA',                          'agent' => $entite->dga,          'icon' => 'fas fa-user-shield', 'color' => 'bg-sky-100 text-sky-700'],
            ['role' => 'Assistante DG',                'agent' => $entite->assistante,   'icon' => 'fas fa-user',        'color' => 'bg-violet-100 text-violet-700'],
            ['role' => 'Sec. DGA',                    'agent' => $entite->dgaSecretaire, 'icon' => 'fas fa-user-pen',   'color' => 'bg-amber-100 text-amber-700'],
        ])->filter(fn ($p) => $p['agent'] !== null)->values();

        // ── Données pour ApexCharts ────────────────────────────────────────
        $evalsDonut = [
            'labels' => ['Validées', 'Soumises', 'Brouillon', 'Refusées'],
            'series' => [$evalsValidees, $evalsSoumises, $evalsBrouillon, $evalsRefusees],
            'colors' => ['#10b981', '#f59e0b', '#94a3b8', '#ef4444'],
        ];

        $fichesDonut = [
            'labels' => ['Acceptées', 'En attente', 'Refusées'],
            'series' => [$fichesAcceptees, $fichesEnAttente, $fichesRefusees],
            'colors' => ['#10b981', '#f59e0b', '#ef4444'],
        ];

        $anneesDisponibles = range(now()->year - 2, now()->year + 1);

        return view('pca.dashboard', compact(
            'entite',
            'annee',
            'anneesDisponibles',
            'dgUser',
            'dgNom',
            'dgInitiale',
            'personnelCabinet',
            // KPIs objectifs
            'totalFiches',
            'fichesAcceptees',
            'fichesEnAttente',
            'fichesRefusees',
            'tauxAvancement',
            // KPIs évaluations
            'evalsTotal',
            'evalsValidees',
            'evalsSoumises',
            'evalsRefusees',
            'evalsBrouillon',
            'noteMoyenne',
            // Charts
            'evalsDonut',
            'fichesDonut',
            // Récents
            'fichesDGRecentes',
        ));
    }
}
