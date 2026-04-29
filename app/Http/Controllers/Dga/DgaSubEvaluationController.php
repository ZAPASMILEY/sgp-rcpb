<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\SubjectiveCriteriaTemplate;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DgaSubEvaluationController extends Controller
{
    use ResolvesEntite;

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /** Retourne les subordonnés du DGA : Directeurs Techniques + secrétaire. */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDGA();
        $subordonnes = collect();

        // Directeurs Techniques
        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')->pluck('directeur_agent_id');
        $dts = User::whereIn('agent_id', $dtAgentIds)->where('role', 'Directeur_Technique')->get();
        foreach ($dts as $dt) {
            $subordonnes->push(['id' => $dt->id, 'nom' => $dt->name, 'role_label' => 'Directeur Technique']);
        }

        // Secrétaire DGA
        if ($entite && $entite->dga_secretaire_agent_id) {
            $sec = User::where('agent_id', $entite->dga_secretaire_agent_id)->first();
            if ($sec) {
                $subordonnes->push(['id' => $sec->id, 'nom' => $sec->name, 'role_label' => 'Secrétaire DGA']);
            }
        }

        return $subordonnes;
    }

    public function create(Request $request): View
    {
        $this->checkDga();


        $subordonnes        = $this->getSubordonnes();
        $preselectedId      = (int) $request->get('subordonne_id', 0);
        $selectedSubordonne = $subordonnes->firstWhere('id', $preselectedId);

        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }

        $objectiveOptions = [];
        if ($selectedSubordonne) {
            $fiches = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', now()->toDateString())
                ->where('assignable_type', User::class)
                ->where('assignable_id', (int) $selectedSubordonne['id'])
                ->orderBy('titre')
                ->get();

            foreach ($fiches as $fiche) {
                $objectiveOptions[] = [
                    'id'            => $fiche->id,
                    'titre'         => $fiche->titre,
                    'date_echeance' => $fiche->date_echeance instanceof \Carbon\Carbon
                        ? $fiche->date_echeance->toDateString()
                        : (string) $fiche->date_echeance,
                    'objectifs'     => $fiche->objectifs->map(fn ($item) => [
                        'source_fiche_objectif_objectif_id' => $item->id,
                        'titre'                             => $item->description,
                    ])->values()->all(),
                ];
            }
        }

        $subjectiveTemplates = $this->buildSubjectiveTemplates();

        $oldFormations = old('identification.formations', [['periode' => '', 'libelle' => '', 'domaine' => '']]);
        if (! is_array($oldFormations) || $oldFormations === []) {
            $oldFormations = [['periode' => '', 'libelle' => '', 'domaine' => '']];
        }

        $oldExperiences = old('identification.experiences', [['periode' => '', 'poste' => '', 'observations' => '']]);
        if (! is_array($oldExperiences) || $oldExperiences === []) {
            $oldExperiences = [['periode' => '', 'poste' => '', 'observations' => '']];
        }

        return view('dga.subordonnes.evaluations.create', compact(
            'subordonnes',
            'selectedSubordonne',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkDga();


        $subordonnes = $this->getSubordonnes();
        $allowedIds  = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'subordonne_id'                    => ['required', 'integer', 'in:'.implode(',', $allowedIds ?: [0])],
            'date_debut'                       => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'date_fin'                         => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.semestre'          => ['required', 'in:1,2'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.emploi'            => ['nullable', 'string', 'max:255'],
            'identification.direction'         => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.formations'            => ['nullable', 'array'],
            'identification.formations.*.periode'  => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle'  => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine'  => ['nullable', 'string', 'max:255'],
            'identification.experiences'               => ['nullable', 'array'],
            'identification.experiences.*.periode'     => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'       => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations'=> ['nullable', 'string', 'max:255'],
            'subjective_criteres'              => ['required', 'array', 'min:1'],
            'objective_criteres'               => ['required', 'array', 'min:1'],
            'points_a_ameliorer'               => ['nullable', 'string'],
            'strategies_amelioration'          => ['nullable', 'string'],
            'commentaire'                      => ['nullable', 'string', 'max:2000'],
            'signature_evalue_nom'             => ['nullable', 'string', 'max:255'],
            'signature_evaluateur_nom'         => ['nullable', 'string', 'max:255'],
            'date_signature_evalue'            => ['nullable', 'date'],
            'date_signature_evaluateur'        => ['nullable', 'date'],
        ]);

        $subordonne = User::findOrFail($validated['subordonne_id']);

        $dateDebut = preg_replace_callback('/^(0[1-9]|1[0-2])\/(\d{4})$/', fn ($m) => $m[2].'-'.$m[1].'-01', $validated['date_debut']);
        $dateFin   = preg_replace_callback('/^(0[1-9]|1[0-2])\/(\d{4})$/', fn ($m) => $m[2].'-'.$m[1].'-01', $validated['date_fin']);

        if (strtotime($dateFin) < strtotime($dateDebut)) {
            return back()->withInput()->withErrors(['date_fin' => 'La date de fin doit être postérieure à la date de début.']);
        }

        $identification = $validated['identification'] ?? [];
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors(['identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
            }
            $identification['date_evaluation'] = $normalized;
        }

        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($row) => [
                'periode' => trim((string) ($row['periode'] ?? '')),
                'libelle' => trim((string) ($row['libelle'] ?? '')),
                'domaine' => trim((string) ($row['domaine'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
            ->values()->all();

        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($row) => [
                'periode'      => trim((string) ($row['periode'] ?? '')),
                'poste'        => trim((string) ($row['poste'] ?? '')),
                'observations' => trim((string) ($row['observations'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
            ->values()->all();

        $normalizedSubjective = $this->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors([
                'subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.',
            ]);
        }

        $scores  = $this->computeScores($normalizedSubjective, $normalizedObjective);
        $anneeId = null;
        try { $anneeId = Annee::resolveIdForDate($dateDebut); } catch (\Throwable) {}

        $user = Auth::user();

        $evaluation = DB::transaction(function () use (
            $user, $subordonne, $dateDebut, $dateFin, $anneeId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => User::class,
                'evaluable_id'              => $subordonne->id,
                'evaluable_role'            => $subordonne->role,
                'annee_id'                  => $anneeId,
                'evaluateur_id'             => $user->id,
                'date_debut'                => $dateDebut,
                'date_fin'                  => $dateFin,
                'moyenne_subjectifs'        => $scores['moyenne_subjectifs'],
                'note_criteres_subjectifs'  => $scores['note_criteres_subjectifs'],
                'moyenne_objectifs'         => $scores['moyenne_objectifs'],
                'note_criteres_objectifs'   => $scores['note_criteres_objectifs'],
                'note_finale'               => $scores['note_finale'],
                'commentaire'               => $validated['commentaire'] ?? null,
                'points_a_ameliorer'        => $validated['points_a_ameliorer'] ?? null,
                'strategies_amelioration'   => $validated['strategies_amelioration'] ?? null,
                'signature_evalue_nom'      => $validated['signature_evalue_nom'] ?? ($identification['nom_prenom'] ?? null),
                'signature_evaluateur_nom'  => $validated['signature_evaluateur_nom'] ?? $user->name,
                'date_signature_evalue'     => $validated['date_signature_evalue'] ?? null,
                'date_signature_evaluateur' => $validated['date_signature_evaluateur'] ?? null,
                'statut'                    => 'brouillon',
            ]);

            $evaluation->identification()->create($identification);

            foreach (array_merge($normalizedSubjective, $normalizedObjective) as $criterion) {
                $critere = $evaluation->criteres()->create([
                    'type'                              => $criterion['type'],
                    'ordre'                             => $criterion['ordre'],
                    'titre'                             => $criterion['titre'],
                    'description'                       => $criterion['description'],
                    'note_globale'                      => $criterion['note_globale'],
                    'observation'                       => $criterion['observation'],
                    'source_template_id'                => $criterion['source_template_id'],
                    'source_fiche_objectif_id'          => $criterion['source_fiche_objectif_id'],
                    'source_fiche_objectif_objectif_id' => $criterion['source_fiche_objectif_objectif_id'],
                ]);
                foreach ($criterion['subcriteria'] as $sub) {
                    $critere->sousCriteres()->create([
                        'ordre'       => $sub['ordre'],
                        'libelle'     => $sub['libelle'],
                        'note'        => $sub['note'],
                        'observation' => $sub['observation'],
                    ]);
                }
            }

            return $evaluation;
        });

        return redirect(route('dga.subordonnes.show', $subordonne).'?tab=evaluations')
            ->with('status', "Évaluation créée pour {$subordonne->name}.");
    }

    public function show(Evaluation $evaluation): View
    {
        $this->checkDga();

        $subordonnes = $this->getSubordonnes();
        if (! $subordonnes->pluck('id')->contains($evaluation->evaluable_id)) {
            abort(403);
        }

        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);

        $subordonne         = $evaluation->evaluable;
        $mention            = $this->mention((float) $evaluation->note_finale);
        $periodeLabel       = $this->periodeLabel($evaluation);
        $cibleLabel         = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $backUrl            = route('dga.subordonnes.show', $subordonne).'?tab=evaluations';
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        return view('dga.subordonnes.evaluations.show', compact(
            'evaluation', 'subordonne', 'mention', 'periodeLabel',
            'cibleLabel', 'backUrl', 'objectiveCriteria', 'subjectiveCriteria'
        ));
    }

    public function submit(Evaluation $evaluation): RedirectResponse
    {
        $this->checkDga();


        $subordonnes = $this->getSubordonnes();
        if (! $subordonnes->pluck('id')->contains($evaluation->evaluable_id)) {
            abort(403);
        }
        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        if ($evaluation->evaluable) {
            Alerte::notifier(
                $evaluation->evaluable_id,
                'Nouvelle fiche d\'évaluation reçue',
                'Le Directeur Général Adjoint vous a soumis une fiche d\'évaluation.',
                'haute'
            );
        }

        return redirect(route('dga.subordonnes.show', $evaluation->evaluable).'?tab=evaluations')
            ->with('status', 'Évaluation soumise avec succès.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $this->checkDga();

        $subordonnes = $this->getSubordonnes();
        if (! $subordonnes->pluck('id')->contains($evaluation->evaluable_id)) {
            abort(403);
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $subordonne = $evaluation->evaluable;
        $evaluation->delete();

        return redirect(route('dga.subordonnes.show', $subordonne).'?tab=evaluations')
            ->with('status', 'Évaluation supprimée.');
    }

    public function exportPdf(Evaluation $evaluation)
    {
        $this->checkDga();


        $subordonnes = $this->getSubordonnes();
        if (! $subordonnes->pluck('id')->contains($evaluation->evaluable_id)) {
            abort(403);
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres', 'evaluable']);
        $evaluable          = $evaluation->evaluable;
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note               = (float) $evaluation->note_finale;
        $mention            = $this->mention($note);
        $cibleLabel         = $evaluation->identification->nom_prenom ?? ($evaluable?->name ?? 'Subordonné');
        $cibleType          = str_replace('_', ' ', $evaluable?->role ?? 'Subordonné');

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-dga.pdf');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function mention(float $note): string
    {
        return $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
    }

    private function periodeLabel(Evaluation $evaluation): string
    {
        $year     = $evaluation->identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
        $semestre = trim((string) ($evaluation->identification?->semestre ?? ''));
        if ($semestre === '') {
            $semestre = $evaluation->date_debut->month <= 6 ? '1' : '2';
        }
        return $year.' - Semestre '.$semestre;
    }

    private function buildSubjectiveTemplates(): array
    {
        return SubjectiveCriteriaTemplate::query()
            ->with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn ($t) => [
                'id'          => $t->id,
                'ordre'       => $t->ordre,
                'titre'       => $t->titre,
                'description' => $t->description,
                'subcriteria' => $t->subcriteria->map(fn ($s) => [
                    'libelle' => $s->libelle,
                    'ordre'   => $s->ordre,
                ])->values()->all(),
            ])
            ->values()->all();
    }

    private function normalizeCriteria(array $criteria, string $type, int $min, int $max, bool $strict = true): array
    {
        $normalized = [];
        foreach (array_values($criteria) as $idx => $criterion) {
            if (! is_array($criterion)) continue;
            $title = trim((string) ($criterion['titre'] ?? ''));
            if ($title === '') continue;

            $subcriteria = [];
            foreach (array_values((array) ($criterion['subcriteria'] ?? [])) as $subIdx => $sub) {
                if (! is_array($sub)) continue;
                $label = trim((string) ($sub['libelle'] ?? ''));
                if ($label === '') { if ($strict) continue; $label = '-'; }
                $subcriteria[] = [
                    'ordre'       => $subIdx + 1,
                    'libelle'     => $label,
                    'note'        => max($min, min($max, (float) ($sub['note'] ?? $min))),
                    'observation' => filled($sub['observation'] ?? null) ? trim((string) $sub['observation']) : null,
                ];
            }

            if ($strict && $subcriteria === []) continue;
            if (! $strict && $subcriteria === []) {
                $subcriteria = [['ordre' => 1, 'libelle' => '-', 'note' => $min, 'observation' => null]];
            }

            $normalized[] = [
                'type'                              => $type,
                'ordre'                             => $idx + 1,
                'titre'                             => $title,
                'description'                       => filled($criterion['description'] ?? null) ? trim((string) $criterion['description']) : null,
                'note_globale'                      => round(collect($subcriteria)->avg('note') ?? 0, 2),
                'observation'                       => filled($criterion['observation'] ?? null) ? trim((string) $criterion['observation']) : null,
                'source_template_id'                => isset($criterion['source_template_id']) ? (int) $criterion['source_template_id'] : null,
                'source_fiche_objectif_id'          => isset($criterion['source_fiche_objectif_id']) ? (int) $criterion['source_fiche_objectif_id'] : null,
                'source_fiche_objectif_objectif_id' => isset($criterion['source_fiche_objectif_objectif_id']) ? (int) $criterion['source_fiche_objectif_objectif_id'] : null,
                'subcriteria'                       => $subcriteria,
            ];
        }
        return $normalized;
    }

    private function computeScores(array $subjectiveCriteria, array $objectiveCriteria): array
    {
        $moyObj  = round(collect($objectiveCriteria)->avg('note_globale') ?? 0, 2);
        $moySubj = round(collect($subjectiveCriteria)->avg('note_globale') ?? 0, 2);
        return [
            'moyenne_objectifs'        => $moyObj,
            'moyenne_subjectifs'       => $moySubj,
            'note_criteres_objectifs'  => round($moyObj * 0.75, 2),
            'note_criteres_subjectifs' => round($moySubj * 0.25, 2),
            'note_finale'              => round(($moyObj * 0.75 + $moySubj * 0.25) * 2, 2),
        ];
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) return $date->toDateString();
            } catch (\Throwable) {}
        }
        return null;
    }
}
