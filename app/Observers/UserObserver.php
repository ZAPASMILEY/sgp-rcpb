<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;

class UserObserver
{
    private const TRACKED = ['role', 'is_active', 'agent_id', 'email'];

    public function created(User $user): void
    {
        AuditLog::record(
            User::class,
            $user->id,
            'created',
            null,
            ['role' => $user->role, 'email' => $user->email, 'is_active' => $user->is_active],
            'Compte créé : '.$user->name.' ('.$user->role.')',
        );
    }

    public function updated(User $user): void
    {
        $dirty = array_intersect_key($user->getDirty(), array_flip(self::TRACKED));
        if (empty($dirty)) {
            return;
        }

        $old = [];
        $new = [];
        foreach ($dirty as $field => $newVal) {
            $old[$field] = $user->getOriginal($field);
            $new[$field] = $newVal;
        }

        $description = 'Compte modifié ('.implode(', ', array_keys($dirty)).')';

        if (isset($dirty['is_active'])) {
            $description = $user->is_active ? 'Compte activé' : 'Compte désactivé';
        } elseif (isset($dirty['role'])) {
            $description = 'Rôle changé : '.$user->getOriginal('role').' → '.$user->role;
        }

        AuditLog::record(User::class, $user->id, 'updated', $old, $new, $description);
    }

    public function deleted(User $user): void
    {
        AuditLog::record(
            User::class,
            $user->id,
            'deleted',
            ['name' => $user->name, 'role' => $user->role, 'email' => $user->email],
            null,
            'Compte supprimé : '.$user->name,
        );
    }
}
