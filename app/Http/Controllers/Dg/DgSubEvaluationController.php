<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DgSubEvaluationController extends Controller
{
    use ResolvesEntite;

    private const ALLOWED_ROLES = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];

    /** Retourne tous les subordonnés du DG connecté. */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDG();
        $subordonnes = collect();

        if (! $entite) {
            return $subordonnes;
        }

        if ($entite->dga_agent_id) {
            $dga = User::where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first();
            if ($dga) {
                $subordonnes->push(['id' => $dga->id, 'nom' => $dga->name, 'role_label' => 'DGA']);
            }
        }

        if ($entite->assistante_agent_id) {
            $assistante = User::where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first();
            if ($assistante) {
                $subordonnes->push(['id' => $assistante->id, 'nom' => $assistante->name, 'role_label' => 'Assistante']);
            }
        }

        $conseillers = User::where('role', 'Conseillers_Dg')->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))->get();
        foreach ($conseillers as $c) {
            $subordonnes->push(['id' => $c->id, 'nom' => $c->name, 'role_label' => 'Conseiller']);
        }

        return $subordonnes;
    }

    public function create(Request $request): View
    {
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403);
        }

        $subordonnes      = $this->getSubordonnes();
        $preselectedId    = (int) $request->get('subordonne_id', 0);
        $selectedSubordonne = $subordonnes->firstWhere('id', $preselectedId);

        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }

        // Build objective options for the selected subordinate
        $objectiveOptions = [];
        if ($selectedSubordonne) {
            $today  = now()->toDateString();
            $fiches = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', $today)
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

        return view('dg.subordonnes.evaluations.create', compact(
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
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403);
        }

        $subordonnes       = $this->getSubordonnes();
        $allowedIds        = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'subordonne_id'                   => ['required', 'integer', 'in:'.implode(',', $allowedIds)],
            'date_debut'                      => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'date_fin'                        => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'identification.nom_prenom'       => ['nullable', 'string', 'max:255'],
            'identification.semestre'         => ['required', 'in:1,2'],
            'identification.date_evaluation'  => ['nullable', 'string', 'max:20'],
            'identification.matricule'        => ['nullable', 'string', 'max:255'],
            'identification.emploi'           => ['nullable', 'string', 'max:255'],
            'identification.direction'        => ['nullable', 'string', 'max:255'],
            'identification.direction_service'=> ['nullable', 'string', 'max:255'],
            'identification.formations'       => ['nullable', 'array'],
            'identification.formations.*.periode' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine' => ['nullable', 'string', 'max:255'],
            'identification.experiences'      => ['nullable', 'array'],
            'identification.experiences.*.periode'      => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'        => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations' => ['nullable', 'string', 'max:255'],
            'subjective_criteres'             => ['required', 'array', 'min:1'],
            'objective_criteres'              => ['required', 'array', 'min:1'],
            'points_a_ameliorer'              => ['nullable', 'string'],
            'strategies_amelioration'         => ['nullable', 'string'],
            'commentaire'                     => ['nullable', 'string', 'max:2000'],
            'signature_evalue_nom'            => ['nullable', 'string', 'max:255'],
            'signature_evaluateur_nom'        => ['nullable', 'string', 'max:255'],
            'date_signature_evalue'           => ['nullable', 'date'],
            'date_signature_evaluateur'       => ['nullable', 'date'],
        ]);

        $subordonne = User::findOrFail($validated['subordonne_id']);
        if (! in_array($subordonne->role, self::ALLOWED_ROLES, true)) {
            abort(403, 'Cible invalide.');
        }

        // Convert MM/YYYY to YYYY-MM-01
        $dateDebut = preg_replace_callback('/^(0[1-9]|1[0-2])\/(\d{4})$/', fn ($m) => $m[2].'-'.$m[1].'-01', $validated['date_debut']);
        $dateFin   = preg_replace_callback('/^(0[1-9]|1[0-2])\/(\d{4})$/', fn ($m) => $m[2].'-'.$m[1].'-01', $validated['date_fin']);

        if (strtotime($dateFin) < strtotime($dateDebut)) {
            return back()->withInput()->withErrors(['date_fin' => 'La date de fin doit être postérieure à la date de début.']);
        }

        // Normalize identification date fields
        $identification = $validated['identification'] ?? [];
        foreach (['date_evaluation'] as $dateField) {
            $raw = $identification[$dateField] ?? null;
            if (! blank($raw)) {
                $normalized = $this->normalizeDateValue($raw);
                if ($normalized === null) {
                    return back()->withInput()->withErrors(["identification.{$dateField}" => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
                }
                $identification[$dateField] = $normalized;
            }
        }

        // Clean formations and experiences
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

        $normalizedSubjective = $this->normalizeCriteria(
            (array) $request->input('subjective_criteres', []),
            'subjectif', 1, 5, false
        );
        $normalizedObjective = $this->normalizeCriteria(
            (array) $request->input('objective_criteres', []),
            'objectif', 1, 5
        );

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors([
                'subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.',
            ]);
        }

        $scores = $this->computeScores($normalizedSubjective, $normalizedObjective);

        try {
            $anneeId = Annee::resolveIdForDate($dateDebut);
        } catch (\Throwable) {
            $anneeId = null;
        }

        $evaluation = DB::transaction(function () use (
            $user, $subordonne, $dateDebut, $dateFin, $anneeId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'           => User::class,
                'evaluable_id'             => $subordonne->id,
                'evaluable_role'           => $subordonne->role,
                'annee_id'                 => $anneeId,
                'evaluateur_id'            => $user->id,
                'date_debut'               => $dateDebut,
                'date_fin'                 => $dateFin,
                'moyenne_subjectifs'       => $scores['moyenne_subjectifs'],
                'note_criteres_subjectifs' => $scores['note_criteres_subjectifs'],
                'moyenne_objectifs'        => $scores['moyenne_objectifs'],
                'note_criteres_objectifs'  => $scores['note_criteres_objectifs'],
                'note_finale'              => $scores['note_finale'],
                'commentaire'              => $validated['commentaire'] ?? null,
                'points_a_ameliorer'       => $validated['points_a_ameliorer'] ?? null,
                'strategies_amelioration'  => $validated['strategies_amelioration'] ?? null,
                'signature_evalue_nom'     => $validated['signature_evalue_nom'] ?? ($identification['nom_prenom'] ?? null),
                'signature_evaluateur_nom' => $validated['signature_evaluateur_nom'] ?? $user->name,
                'date_signature_evalue'    => $validated['date_signature_evalue'] ?? null,
                'date_signature_evaluateur'=> $validated['date_signature_evaluateur'] ?? null,
                'statut'                   => 'brouillon',
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

        $redirect = match ($subordonne->role) {
            'DGA'            => route('dg.dga').'?tab=evaluations',
            'Assistante_Dg'  => route('dg.assistante').'?tab=evaluations',
            default          => route('dg.conseillers.show', $subordonne).'?tab=evaluations',
        };

        return redirect($redirect)->with('status', "Évaluation créée pour {$subordonne->name}.");
    }

    public function show(Evaluation $evaluation): View
    {
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403);
        }

        // Vérifier que c'est bien une évaluation DG→subordonné
        if (
            $evaluation->evaluable_type !== User::class ||
            ! in_array($evaluation->evaluable->role ?? '', self::ALLOWED_ROLES, true)
        ) {
            abort(403);
        }

        if ((int) ($evaluation->evaluable->agent?->entite_id ?? 0) !== (int) ($this->getEntiteForDG()?->id ?? 0)) {
            abort(403);
        }

        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);

        $subordonne        = $evaluation->evaluable;
        $mention           = $this->mention((float) $evaluation->note_finale);
        $periodeLabel      = $this->periodeLabel($evaluation);
        $cibleLabel        = $this->cibleLabel($evaluation);
        $backUrl           = $this->backUrlForSubordonne($subordonne);
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria= $evaluation->criteres->where('type', 'subjectif')->values();

        return view('dg.subordonnes.evaluations.show', compact(
            'evaluation',
            'subordonne',
            'mention',
            'periodeLabel',
            'cibleLabel',
            'backUrl',
            'objectiveCriteria',
            'subjectiveCriteria'
        ));
    }

    public function exportPdf(Evaluation $evaluation)
    {
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403);
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres', 'evaluable']);
        $evaluable = $evaluation->evaluable;

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note       = (float) $evaluation->note_finale;
        $mention    = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel = $evaluation->identification->nom_prenom ?? ($evaluable?->name ?? 'Subordonné');
        $roleLabels = [
            'DGA'            => 'Directeur Général Adjoint',
            'Assistante_Dg'  => 'Assistante DG',
            'Conseillers_Dg' => 'Conseiller DG',
        ];
        $cibleType = $roleLabels[$evaluable?->role ?? ''] ?? ($evaluable?->role ?? 'Subordonné');

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-sub.pdf');
    }

    public function destroy(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403);
        }
        if (
            $evaluation->evaluable_type !== User::class ||
            ! in_array($evaluation->evaluable->role ?? '', self::ALLOWED_ROLES, true)
        ) {
            abort(403);
        }
        if ((int) ($evaluation->evaluable->agent?->entite_id ?? 0) !== (int) ($this->getEntiteForDG()?->id ?? 0)) {
            abort(403);
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $subordonne = $evaluation->evaluable;
        $evaluation->delete();

        $redirect = match ($subordonne->role) {
            'DGA'           => route('dg.dga').'?tab=evaluations',
            'Assistante_Dg' => route('dg.assistante').'?tab=evaluations',
            default         => route('dg.conseillers.show', $subordonne).'?tab=evaluations',
        };

        return redirect($redirect)->with('status', 'Évaluation supprimée.');
    }

    public function submit(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403);
        }
        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }
        $evaluation->statut = 'soumis';
        $evaluation->save();

        $subordonne = $evaluation->evaluable;

        // Notifier le subordonné évalué
        if ($subordonne) {
            Alerte::notifier(
                $subordonne->id,
                'Nouvelle fiche d\'évaluation reçue',
                'Le Directeur Général vous a soumis une fiche d\'évaluation. Connectez-vous pour la consulter.',
                'haute'
            );
        }

        $redirect   = match ($subordonne->role) {
            'DGA'           => route('dg.dga').'?tab=evaluations',
            'Assistante_Dg' => route('dg.assistante').'?tab=evaluations',
            default         => route('dg.conseillers.show', $subordonne).'?tab=evaluations',
        };

        return redirect($redirect)->with('status', 'Évaluation soumise avec succès.');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function mention(float $note): string
    {
        return $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
    }

    private function periodeLabel(Evaluation $evaluation): string
    {
        $identification = $evaluation->identification;
        $year    = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
        $semestre = trim((string) ($identification?->semestre ?? ''));

        if ($semestre === '') {
            $semestre = $evaluation->date_debut->month <= 6 ? '1' : '2';
        }

        return $year.' - Semestre '.$semestre;
    }

    private function cibleLabel(Evaluation $evaluation): string
    {
        $nomPrenom = trim((string) ($evaluation->identification?->nom_prenom ?? ''));

        return $nomPrenom !== '' ? $nomPrenom : ($evaluation->evaluable?->name ?? '-');
    }

    private function backUrlForSubordonne(User $subordonne): string
    {
        return match ($subordonne->role) {
            'DGA'           => route('dg.dga').'?tab=evaluations',
            'Assistante_Dg' => route('dg.assistante').'?tab=evaluations',
            default         => route('dg.conseillers.show', $subordonne).'?tab=evaluations',
        };
    }

    /** @return array<int, array<string, mixed>> */
    private function buildSubjectiveTemplates(): array
    {
        return SubjectiveCriteriaTemplate::query()
            ->with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn (SubjectiveCriteriaTemplate $template) => [
                'id'          => $template->id,
                'ordre'       => $template->ordre,
                'titre'       => $template->titre,
                'description' => $template->description,
                'subcriteria' => $template->subcriteria->map(fn ($sub) => [
                    'libelle' => $sub->libelle,
                    'ordre'   => $sub->ordre,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function normalizeCriteria(array $criteria, string $type, int $minNote, int $maxNote, bool $strict = true): array
    {
        $normalized = [];

        foreach (array_values($criteria) as $idx => $criterion) {
            if (! is_array($criterion)) {
                continue;
            }

            $title = trim((string) ($criterion['titre'] ?? ''));
            if ($title === '') {
                continue;
            }

            $subcriteria = [];
            foreach (array_values((array) ($criterion['subcriteria'] ?? [])) as $subIdx => $sub) {
                if (! is_array($sub)) {
                    continue;
                }
                $label = trim((string) ($sub['libelle'] ?? ''));
                if ($label === '') {
                    if ($strict) {
                        continue;
                    }
                    $label = '-';
                }
                $note = max($minNote, min($maxNote, (float) ($sub['note'] ?? $minNote)));
                $subcriteria[] = [
                    'ordre'       => $subIdx + 1,
                    'libelle'     => $label,
                    'note'        => $note,
                    'observation' => filled($sub['observation'] ?? null) ? trim((string) $sub['observation']) : null,
                ];
            }

            if ($strict && $subcriteria === []) {
                continue;
            }
            if (! $strict && $subcriteria === []) {
                $subcriteria = [['ordre' => 1, 'libelle' => '-', 'note' => $minNote, 'observation' => null]];
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

    /** @return array<string, float> */
    private function computeScores(array $subjectiveCriteria, array $objectiveCriteria): array
    {
        $moyObj  = round(collect($objectiveCriteria)->avg('note_globale') ?? 0, 2);
        $moySubj = round(collect($subjectiveCriteria)->avg('note_globale') ?? 0, 2);
        $noteObj = round($moyObj * 0.75, 2);
        $noteSubj= round($moySubj * 0.25, 2);

        return [
            'moyenne_objectifs'        => $moyObj,
            'moyenne_subjectifs'       => $moySubj,
            'note_criteres_objectifs'  => $noteObj,
            'note_criteres_subjectifs' => $noteSubj,
            'note_finale'              => round(($noteObj + $noteSubj) * 2, 2),
        ];
    }

    private function normalizeDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                // continue
            }
        }
        return null;
    }
}
