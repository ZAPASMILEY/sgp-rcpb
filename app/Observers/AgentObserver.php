<?php

namespace App\Observers;

use App\Models\Agent;
use App\Models\AuditLog;

class AgentObserver
{
    private const TRACKED = [
        'role',
        'direction_id', 'delegation_technique_id',
        'caisse_id', 'agence_id', 'guichet_id', 'service_id',
    ];

    public function created(Agent $agent): void
    {
        AuditLog::record(
            Agent::class,
            $agent->id,
            'created',
            null,
            ['nom' => $agent->nom, 'prenom' => $agent->prenom, 'role' => $agent->role],
            'Agent créé : '.$agent->prenom.' '.$agent->nom.' ('.$agent->role.')',
        );
    }

    public function updated(Agent $agent): void
    {
        $dirty = array_intersect_key($agent->getDirty(), array_flip(self::TRACKED));
        if (empty($dirty)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($dirty as $field => $newVal) {
            $old[$field] = $agent->getOriginal($field);
            $new[$field] = $newVal;
        }

        $description = isset($dirty['role'])
            ? 'Rôle changé : '.$agent->getOriginal('role').' → '.$agent->role
            : 'Affectation modifiée ('.implode(', ', array_keys($dirty)).')';

        AuditLog::record(Agent::class, $agent->id, 'updated', $old, $new, $description);
    }

    public function deleted(Agent $agent): void
    {
        AuditLog::record(
            Agent::class,
            $agent->id,
            'deleted',
            ['nom' => $agent->nom, 'prenom' => $agent->prenom, 'role' => $agent->role],
            null,
            'Agent supprimé : '.$agent->prenom.' '.$agent->nom,
        );
    }
}
