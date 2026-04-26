<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Utilisateurs ayant cette permission directement.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')->withTimestamps();
    }

    /**
     * Rôles ayant cette permission (via roles_has_permissions).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_has_permissions', 'permissions_id', 'roles_id')
                    ->withTimestamps();
    }
}