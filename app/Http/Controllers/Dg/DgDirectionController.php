<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\SubjectiveCriteriaTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DgDirectionController extends Controller
{
    // ── Helpers ────────────────────────────────────────────────────────────

    private function getEntiteId(): int
    {
        return (int) Auth::user()->pca_entite_id;
    }

    private function getDirections(): \Illuminate\Support\Collection
    {
        return Direction::where('entite_id', $this->getEntiteId())
            ->with(['user', 'services'])
            ->orderBy('nom')
            ->get();
    }

    private function authorizeDirection(Direction $direction): void
    {
        if ((int) $direction->entite_id !== $this->getEntiteId()) {
            abort(403);
        }
    }

    private function authorizeEvaluation(Evaluation $evaluation): Direction
    {
        if (
            $evaluation->evaluable_type !== Direction::class ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager' ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403);
        }

        $direction = Direction::find($evaluation->evaluable_id);
        if (! $direction || (int) $direction->entite_id !== $this->getEntiteId()) {
            abort(403);
        }

        return $direction;
    }

    private function authorizeObjectif(FicheObjectif $fiche): Direction
    {
        if (
            $fiche->assignable_type !== Direction::class
        ) {
            abort(403);
        }

        $direction = Direction::find($fiche->assignable_id);
        if (! $direction || (int) $direction->entite_id !== $this->getEntiteId()) {
            abort(403);
        }

        return $direction;
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(): View
    {
        $directions = $this->getDirections();

        return view('dg.directions.index', compact('directions'));
    }

    // ── Show direction ─────────────────────────────────────────────────────

    public function show(Request $request, Direction $direction): View
    {
        $this->authorizeDirection($direction);

        $tab = $request->get('tab', 'evaluations');

        $evaluations = Evaluation::where('evaluable_type', Direction::class)
            ->where('evaluable_id', $direction->id)
            ->where(fn ($q) => $q->where('evaluable_role', 'manager')->orWhere('evaluable_role', 'Manager'))
            ->where('evaluateur_id', Auth::id())
            ->with('identification')
            ->orderByDesc('date_debut')
            ->get();

        $fiches = FicheObjectif::where('assignable_type', Direction::class)
            ->where('assignable_id', $direction->id)
            ->withCount('objectifs')
            ->orderByDesc('date')
            ->get();

        return view('dg.directions.show', compact('direction', 'tab', 'evaluations', 'fiches'));
    }

    // ── Objectifs ──────────────────────────────────────────────────────────

    public function createObjectif(Request $request, Direction $direction): View
    {
        $this->authorizeDirection($direction);

        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('dg.directions.objectifs.create', compact('direction', 'oldObjectifs'));
    }

    public function storeObjectif(Request $request): RedirectResponse
    {
        $directionIds = Direction::where('entite_id', $this->getEntiteId())->pluck('id')->all();

        $validated = $request->validate([
            'direction_id'  => ['required', 'integer', 'in:'.implode(',', $directionIds ?: [0])],
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $direction = Direction::findOrFail($validated['direction_id']);

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
            'assignable_type'       => Direction::class,
            'assignable_id'         => $direction->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => 'en_attente',
        ]);

        foreach ($validated['objectifs'] as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        // Notifier le directeur
        if ($direction->user_id) {
            Alerte::notifier(
                (int) $direction->user_id,
                'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} ». Connectez-vous pour l'examiner.",
                'haute'
            );
        }

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs assignée à la direction « {$direction->nom} ».");
    }

    public function showObjectif(FicheObjectif $fiche): View
    {
        $direction = $this->authorizeObjectif($fiche);
        $fiche->load('objectifs');

        $statusClass = match ($fiche->statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default      => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($fiche->statut) {
            'acceptee'   => 'Acceptée',
            'en_attente' => 'En attente',
            'refusee'    => 'Refusée',
            default      => ucfirst((string) ($fiche->statut ?? 'En attente')),
        };

        return view('dg.directions.objectifs.show', compact('fiche', 'direction', 'statusClass', 'statusLabel'));
    }

    public function destroyObjectif(FicheObjectif $fiche): RedirectResponse
    {
        $direction = $this->authorizeObjectif($fiche);
        $fiche->delete();

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs'])
            ->with('status', 'Fiche d\'objectifs supprimée.');
    }

    // ── Évaluations ────────────────────────────────────────────────────────

    public function createEvaluation(Request $request, Direction $direction): View
    {
        $this->authorizeDirection($direction);

        $today = now()->toDateString();
        $fiches = FicheObjectif::query()
            ->with('objectifs')
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', Direction::class)
            ->where('assignable_id', $direction->id)
            ->orderBy('titre')
            ->get();

        $objectiveOptions = $fiches->map(fn ($f) => [
            'id'            => $f->id,
            'titre'         => $f->titre,
            'date_echeance' => $f->date_echeance instanceof Carbon
                ? $f->date_echeance->toDateString()
                : (string) $f->date_echeance,
            'objectifs'     => $f->objectifs->map(fn ($item) => [
                'source_fiche_objectif_objectif_id' => $item->id,
                'titre'                             => $item->description,
            ])->values()->all(),
        ])->values()->all();

        $subjectiveTemplates = $this->buildSubjectiveTemplates();

        $oldFormations = old('identification.formations', [['periode' => '', 'libelle' => '', 'domaine' => '']]);
        if (! is_array($oldFormations) || $oldFormations === []) {
            $oldFormations = [['periode' => '', 'libelle' => '', 'domaine' => '']];
        }

        $oldExperiences = old('identification.experiences', [['periode' => '', 'poste' => '', 'observations' => '']]);
        if (! is_array($oldExperiences) || $oldExperiences === []) {
            $oldExperiences = [['periode' => '', 'poste' => '', 'observations' => '']];
        }

        $displayYear = now()->year;

        return view('dg.directions.evaluations.create', compact(
            'direction',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
            'displayYear',
        ));
    }

    public function storeEvaluation(Request $request): RedirectResponse
    {
        $user         = Auth::user();
        $directionIds = Direction::where('entite_id', $this->getEntiteId())->pluck('id')->all();

        $validated = $request->validate([
            'direction_id'                     => ['required', 'integer', 'in:'.implode(',', $directionIds ?: [0])],
            'date_debut'                       => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'date_fin'                         => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.semestre'          => ['required', 'in:1,2'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.emploi'            => ['nullable', 'string', 'max:255'],
            'identification.direction'         => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.formations'        => ['nullable', 'array'],
            'identification.formations.*.periode' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine' => ['nullable', 'string', 'max:255'],
            'identification.experiences'       => ['nullable', 'array'],
            'identification.experiences.*.periode'      => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'        => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations' => ['nullable', 'string', 'max:255'],
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

        $direction = Direction::findOrFail($validated['direction_id']);

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
            return back()->withInput()->withErrors(['subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.']);
        }

        $scores = $this->computeScores($normalizedSubjective, $normalizedObjective);

        try {
            $anneeId = Annee::resolveIdForDate($dateDebut);
        } catch (\Throwable) {
            $anneeId = null;
        }

        $evaluation = DB::transaction(function () use (
            $user, $direction, $dateDebut, $dateFin, $anneeId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => Direction::class,
                'evaluable_id'              => $direction->id,
                'evaluable_role'            => 'manager',
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

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations'])
            ->with('status', "Évaluation créée pour la direction « {$direction->nom} ».");
    }

    public function showEvaluation(Evaluation $evaluation): View
    {
        $direction = $this->authorizeEvaluation($evaluation);
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $ident   = $evaluation->identification;

        $statusClass = match ($evaluation->statut) {
            'valide'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soumis'    => 'border-amber-200 bg-amber-50 text-amber-700',
            'refuse'    => 'border-rose-200 bg-rose-50 text-rose-700',
            'brouillon' => 'border-slate-200 bg-slate-100 text-slate-700',
            default     => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($evaluation->statut) {
            'valide'    => 'Acceptée',
            'soumis'    => 'Soumise',
            'refuse'    => 'Refusée',
            'brouillon' => 'Brouillon',
            default     => ucfirst((string) $evaluation->statut),
        };

        return view('dg.directions.evaluations.show', compact(
            'evaluation',
            'direction',
            'objectiveCriteria',
            'subjectiveCriteria',
            'note',
            'mention',
            'ident',
            'statusClass',
            'statusLabel',
        ));
    }

    public function submitEvaluation(Evaluation $evaluation): RedirectResponse
    {
        $direction = $this->authorizeEvaluation($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->statut = 'soumis';
        $evaluation->save();

        // Notifier le directeur
        if ($direction->user_id) {
            Alerte::notifier(
                (int) $direction->user_id,
                'Nouvelle fiche d\'évaluation reçue',
                'Le Directeur Général vous a soumis une fiche d\'évaluation. Connectez-vous pour la consulter.',
                'haute'
            );
        }

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations'])
            ->with('status', 'Évaluation soumise au directeur.');
    }

    public function destroyEvaluation(Evaluation $evaluation): RedirectResponse
    {
        $direction = $this->authorizeEvaluation($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $evaluation->delete();

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations'])
            ->with('status', 'Évaluation supprimée.');
    }

    public function exportEvaluationPdf(Evaluation $evaluation)
    {
        $direction = $this->authorizeEvaluation($evaluation);
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note       = (float) $evaluation->note_finale;
        $mention    = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel = $evaluation->identification?->nom_prenom ?? trim($direction->directeur_prenom.' '.$direction->directeur_nom);
        $cibleType  = 'Directeur — '.$direction->nom;

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-direction-'.$evaluation->id.'.pdf');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function buildSubjectiveTemplates(): array
    {
        return SubjectiveCriteriaTemplate::query()
            ->with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn ($template) => [
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
                $note          = max($minNote, min($maxNote, (float) ($sub['note'] ?? $minNote)));
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

    private function computeScores(array $subjectiveCriteria, array $objectiveCriteria): array
    {
        $moyObj  = round(collect($objectiveCriteria)->avg('note_globale') ?? 0, 2);
        $moySubj = round(collect($subjectiveCriteria)->avg('note_globale') ?? 0, 2);
        $noteObj  = round($moyObj * 0.75, 2);
        $noteSubj = round($moySubj * 0.25, 2);

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
            }
        }

        return null;
    }
}
