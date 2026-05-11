<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\SubjectiveCriteriaTemplate;
use Carbon\Carbon;

/**
 * Shared evaluation computation helpers extracted from DgSubEvaluationController,
 * DgaSubEvaluationController, DgDirectionController, DirecteurEvaluationController,
 * DirecteurSubordonneController, and PcaEvaluationController.
 */
class EvaluationService
{
    /**
     * Convert a raw score (0–10) into a mention label.
     */
    public function mention(float $score): string
    {
        return $score >= 8.5 ? 'Excellent' : ($score >= 7 ? 'Bien' : ($score >= 5 ? 'Passable' : 'Insuffisant'));
    }

    /**
     * Build the period label (e.g. "2025 - Semestre 1") from an evaluation.
     */
    public function periodeLabel(Evaluation $evaluation): string
    {
        $identification = $evaluation->identification;
        $year     = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
        $semestre = trim((string) ($identification?->semestre ?? ''));

        if ($semestre === '') {
            $semestre = $evaluation->date_debut->month <= 6 ? '1' : '2';
        }

        return $year.' - Semestre '.$semestre;
    }

    /**
     * Fetch all active subjective criterion templates, serialised for use in forms/JS.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildSubjectiveTemplates(): array
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

    /**
     * Sanitise and reshape raw criterion rows from the form submission.
     *
     * @param  array<int, mixed>  $criteria
     * @param  bool  $strict  When false (subjective mode): empty sub-libelles become '-'
     *                        and criteria with no subcriteria keep a default placeholder row.
     * @return array<int, array<string, mixed>>
     */
    public function normalizeCriteria(array $criteria, string $type, int $minNote, int $maxNote, bool $strict = true): array
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

    /**
     * Compute weighted scores from normalised criterion arrays.
     * Objectifs = 75 %, Subjectifs = 25 %. Final note is on 10.
     *
     * @param  array<int, array<string, mixed>>  $subjectiveCriteria
     * @param  array<int, array<string, mixed>>  $objectiveCriteria
     * @return array<string, float>
     */
    public function computeScores(array $subjectiveCriteria, array $objectiveCriteria): array
    {
        $moyObj  = round(collect($objectiveCriteria)->avg('note_globale') ?? 0, 2);
        $moySubj = round(collect($subjectiveCriteria)->avg('note_globale') ?? 0, 2);
        $noteObj  = round($moyObj  * 0.75, 2);
        $noteSubj = round($moySubj * 0.25, 2);

        return [
            'moyenne_objectifs'        => $moyObj,
            'moyenne_subjectifs'       => $moySubj,
            'note_criteres_objectifs'  => $noteObj,
            'note_criteres_subjectifs' => $noteSubj,
            'note_finale'              => round(($noteObj + $noteSubj) * 2, 2),
        ];
    }

    /**
     * Parse a date string in Y-m-d or d/m/Y format and return a Y-m-d string,
     * or null if the value is blank or unrecognised.
     */
    public function normalizeDateValue(mixed $value): ?string
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
                // try next format
            }
        }

        return null;
    }

    /**
     * Walk a list of dot-notation paths, normalise each date value in $payload,
     * and throw a ValidationException if any path contains an invalid date.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>    $paths
     * @return array<string, mixed>
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function normalizePayloadDates(array $payload, array $paths): array
    {
        $errors = [];

        foreach ($paths as $path) {
            $raw = data_get($payload, $path);

            if (blank($raw)) {
                data_set($payload, $path, null);
                continue;
            }

            $normalized = $this->normalizeDateValue($raw);

            if ($normalized === null) {
                $errors[$path] = 'Format de date invalide. Utilisez JJ/MM/AAAA ou AAAA-MM-JJ.';
                continue;
            }

            data_set($payload, $path, $normalized);
        }

        if ($errors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $payload;
    }

    /**
     * Persist normalised criteria (and their sub-criteria) onto an evaluation.
     *
     * @param  array<int, array<string, mixed>>  $criteria  Output of normalizeCriteria()
     */
    public function persistCriteria(Evaluation $evaluation, array $criteria): void
    {
        foreach ($criteria as $criterion) {
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
    }
}
