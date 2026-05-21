<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Evaluation;

class EvaluationObserver
{
    // Champs dont les modifications sont auditées
    private const TRACKED = [
        'statut', 'note_finale', 'note_manuelle', 'commentaire',
        'points_a_ameliorer', 'evaluateur_id',
        'date_signature_evalue', 'date_signature_directeur', 'date_signature_evaluateur',
    ];

    public function created(Evaluation $evaluation): void
    {
        AuditLog::record(
            Evaluation::class,
            $evaluation->id,
            'created',
            null,
            ['statut' => $evaluation->statut, 'evaluateur_id' => $evaluation->evaluateur_id],
            'Évaluation créée (statut : '.$evaluation->statut.')',
        );
    }

    public function updated(Evaluation $evaluation): void
    {
        $dirty = array_intersect_key($evaluation->getDirty(), array_flip(self::TRACKED));
        if (empty($dirty)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($dirty as $field => $newVal) {
            $old[$field] = $evaluation->getOriginal($field);
            $new[$field] = $newVal;
        }

        // Description spéciale pour les changements de statut
        $action      = isset($dirty['statut']) ? 'statut_change' : 'updated';
        $description = isset($dirty['statut'])
            ? 'Statut changé : '.$evaluation->getOriginal('statut').' → '.$evaluation->statut
            : 'Évaluation modifiée ('.implode(', ', array_keys($dirty)).')';

        AuditLog::record(Evaluation::class, $evaluation->id, $action, $old, $new, $description);
    }

    public function deleted(Evaluation $evaluation): void
    {
        AuditLog::record(
            Evaluation::class,
            $evaluation->id,
            'deleted',
            ['statut' => $evaluation->statut, 'note_finale' => $evaluation->note_finale],
            null,
            'Évaluation supprimée (statut : '.$evaluation->statut.')',
        );
    }
}
