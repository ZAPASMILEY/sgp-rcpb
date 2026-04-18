<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\SubjectiveCriteriaTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DirecteurEvaluationController extends Controller
{
    // ── Authorization helpers ──────────────────────────────────────────────

    private function getDirection(): Direction
    {
        $direction = Direction::where('user_id', Auth::id())->first();
        if (! $direction) {
            abort(403, 'Aucune direction associée à votre compte.');
        }

        return $direction;
    }

    private function authorizeReceivedEval(Evaluation $evaluation): Direction
    {
        $direction = $this->getDirection();
        if (
            $evaluation->evaluable_type !== Direction::class ||
            (int) $evaluation->evaluable_id !== $direction->id ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager'
        ) {
            abort(403);
        }

        return $direction;
    }

    private function authorizeCreatedEval(Evaluation $evaluation): Direction
    {
        $direction = $this->getDirection();
        if (
            $evaluation->evaluable_type !== Service::class ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager' ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403);
        }
        $service = Service::find($evaluation->evaluable_id);
        if (! $service || (int) $service->direction_id !== $direction->id) {
            abort(403);
        }

        return $direction;
    }

    // ── Créer une évaluation pour un chef de service ──────────────────────

    public function create(Request $request): View
    {
        $direction = $this->getDirection();

        $services = Service::where('direction_id', $direction->id)
            ->orderBy('nom')
            ->get();

        $preselectedId   = (int) $request->get('service_id', 0);
        $selectedService = $services->firstWhere('id', $preselectedId);

        if (! $selectedService && $services->count() === 1) {
            $selectedService = $services->first();
        }

        $objectiveOptions = [];
        if ($selectedService) {
            $today = now()->toDateString();
            $fiches = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', $today)
                ->where('assignable_type', Service::class)
                ->where('assignable_id', $selectedService->id)
                ->orderBy('titre')
                ->get();

            foreach ($fiches as $fiche) {
                $objectiveOptions[] = [
                    'id'            => $fiche->id,
                    'titre'         => $fiche->titre,
                    'date_echeance' => $fiche->date_echeance instanceof Carbon
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

        return view('directeur.evaluations.create', compact(
            'direction',
            'services',
            'selectedService',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $direction  = $this->getDirection();
        $user       = Auth::user();
        $serviceIds = Service::where('direction_id', $direction->id)->pluck('id')->all();

        $validated = $request->validate([
            'service_id'                       => ['required', 'integer', 'in:'.implode(',', $serviceIds ?: [0])],
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

        $service = Service::findOrFail($validated['service_id']);

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
            $user, $service, $dateDebut, $dateFin, $anneeId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => Service::class,
                'evaluable_id'              => $service->id,
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
            ->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('status', "Évaluation créée pour le chef du service « {$service->nom} ».");
    }

    public function show(Evaluation $evaluation): View
    {
        $direction = $this->getDirection();

        $isReceived = $evaluation->evaluable_type === Direction::class
            && (int) $evaluation->evaluable_id === $direction->id
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        $isCreated = $evaluation->evaluable_type === Service::class
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager'
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }

        if ($isCreated) {
            $service = Service::find($evaluation->evaluable_id);
            if (! $service || (int) $service->direction_id !== $direction->id) {
                abort(403);
            }
        }

        $evaluation->load(['evaluateur', 'evaluable', 'identification', 'criteres.sousCriteres']);

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        $note       = (float) $evaluation->note_finale;
        $mention    = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel = $evaluation->identification?->nom_prenom
            ?? ($isReceived
                ? trim($direction->directeur_prenom.' '.$direction->directeur_nom)
                : ($evaluation->evaluable?->nom ?? '-'));
        $cibleType  = $isReceived ? 'Directeur' : 'Chef de service — '.$evaluation->evaluable?->nom;

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

        $ident = $evaluation->identification;

        return view('directeur.evaluations.show', compact(
            'evaluation',
            'direction',
            'isReceived',
            'isCreated',
            'objectiveCriteria',
            'subjectiveCriteria',
            'note',
            'mention',
            'cibleLabel',
            'cibleType',
            'statusClass',
            'statusLabel',
            'ident',
        ));
    }

    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeReceivedEval($evaluation);

        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', 'Cette action n\'est possible que sur une évaluation soumise.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action            = $request->input('action');
        $evaluation->statut = $action === 'accepter' ? 'valide' : 'refuse';
        $evaluation->save();

        if ($evaluation->evaluateur_id) {
            $directeur   = Auth::user();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                (int) $evaluation->evaluateur_id,
                "Fiche d'évaluation {$actionLabel}e par le Directeur",
                "Le Directeur {$directeur?->name} a {$actionLabel} la fiche d'évaluation que vous lui avez soumise.",
                $action === 'accepter' ? 'moyenne' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';

        return redirect()->route('directeur.evaluations.show', $evaluation)->with('status', $msg);
    }

    public function submit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->statut = 'soumis';
        $evaluation->save();

        return redirect()
            ->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('status', 'Évaluation soumise au chef de service.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $evaluation->delete();

        return redirect()
            ->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('status', 'Évaluation supprimée.');
    }

    public function exportPdf(Evaluation $evaluation)
    {
        $direction = $this->getDirection();

        $isReceived = $evaluation->evaluable_type === Direction::class && (int) $evaluation->evaluable_id === $direction->id;
        $isCreated  = $evaluation->evaluable_type === Service::class && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }

        $evaluation->load(['evaluateur', 'evaluable', 'identification', 'criteres.sousCriteres']);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note               = (float) $evaluation->note_finale;
        $mention            = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel         = $evaluation->identification?->nom_prenom ?? '-';
        $cibleType          = $isReceived ? 'Directeur' : 'Chef de service';

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-directeur.pdf');
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
                $note        = max($minNote, min($maxNote, (float) ($sub['note'] ?? $minNote)));
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
