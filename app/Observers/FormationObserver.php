<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Formation;

class FormationObserver
{
    private const TRACKED = ['theme', 'domaine', 'date_debut', 'date_fin', 'duree_heures', 'agent_id'];

    public function created(Formation $formation): void
    {
        AuditLog::record(
            Formation::class,
            $formation->id,
            'created',
            null,
            [
                'theme'        => $formation->theme,
                'domaine'      => $formation->domaine,
                'date_debut'   => $formation->date_debut?->format('d/m/Y'),
                'date_fin'     => $formation->date_fin?->format('d/m/Y'),
                'duree_heures' => $formation->duree_heures,
                'agent_id'     => $formation->agent_id,
            ],
            'Formation créée : «'.$formation->theme.'»',
        );
    }

    public function updated(Formation $formation): void
    {
        $dirty = array_intersect_key($formation->getDirty(), array_flip(self::TRACKED));
        if (empty($dirty)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($dirty as $field => $newVal) {
            $old[$field] = $formation->getOriginal($field);
            $new[$field] = $newVal;
        }

        AuditLog::record(
            Formation::class,
            $formation->id,
            'updated',
            $old,
            $new,
            'Formation modifiée : «'.$formation->theme.'» ('.implode(', ', array_keys($dirty)).')',
        );
    }

    public function deleted(Formation $formation): void
    {
        AuditLog::record(
            Formation::class,
            $formation->id,
            'deleted',
            ['theme' => $formation->theme, 'domaine' => $formation->domaine, 'agent_id' => $formation->agent_id],
            null,
            'Formation supprimée : «'.$formation->theme.'»',
        );
    }
}
