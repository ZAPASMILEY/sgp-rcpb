<?php

namespace App\Services;

use App\Models\FicheObjectif;

/**
 * Shared objectif helpers — ownership guards and avancement updates.
 */
class ObjectifService
{
    /**
     * Abort 403 unless the fiche is assigned to the given user.
     * Used by DG, DGA, and Directeur when updating their own received objectifs.
     */
    public function assertUserOwns(FicheObjectif $fiche, int $userId): void
    {
        if (
            $fiche->assignable_type !== \App\Models\User::class ||
            (int) $fiche->assignable_id !== $userId
        ) {
            abort(403, "Vous ne pouvez modifier l'avancement que de vos propres objectifs.");
        }
    }

    /**
     * Abort 403 unless the fiche is assigned to the given polymorphic target.
     * Used when validating that a manager is looking at a fiche belonging to
     * one of their own subordinates (entity or user).
     */
    public function assertOwnership(FicheObjectif $fiche, string $assignableType, int $assignableId): void
    {
        if (
            $fiche->assignable_type !== $assignableType ||
            (int) $fiche->assignable_id !== $assignableId
        ) {
            abort(403);
        }
    }

    /**
     * Persist a validated avancement value (multiple of 5, 0–100).
     * Callers are responsible for authorising the request first.
     */
    public function updateAvancement(FicheObjectif $fiche, int $percentage): void
    {
        $fiche->avancement_percentage = $percentage;
        $fiche->save();
    }
}
