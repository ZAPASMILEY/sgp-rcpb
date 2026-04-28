<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Agent;
class User extends Authenticatable{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'name',
        'email',
        'password',
        'role',
        'manager_id',

        'must_change_password',
        'theme_preference',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordonnesDirects(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluateur_id');
    }

    public function alertes()
    {
        return $this->belongsToMany(Alerte::class, 'alerte_user')
            ->withPivot('lu', 'lu_at')
            ->withTimestamps();
    }

    public function alertesNonLues()
    {
        return $this->alertes()->wherePivot('lu', false);
    }
// Relation avec les logs d'audit (Bloc 5)
public function activites(): HasMany
{
    return $this->hasMany(Activite::class);
}

// Relation N:N vers les rôles
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
}

public function hasRole(string $roleSlug): bool
{
    return $this->roles()->where('slug', $roleSlug)->exists();
}

// Relation N:N vers les permissions
public function permissions(): BelongsToMany
{
    return $this->belongsToMany(Permission::class, 'permission_user')->withTimestamps();
}

public function hasPermission(string $permissionName): bool
{
    return $this->permissions()->where('name', $permissionName)->exists();
}
    // Subordonnés du DG (DGA, assistante DG)
    public function subordonnes()
    {
        $entite = $this->entite;
        if (!$entite) {
            return collect();
        }
        $dgaId = null;
        if (!empty($entite->dga_email)) {
            $dga = User::where('email', $entite->dga_email)->first();
            $dgaId = $dga?->id;
        }
        $assistanteId = null;
        if (!empty($entite->assistante_dg_email)) {
            $assistante = User::where('email', $entite->assistante_dg_email)->first();
            $assistanteId = $assistante?->id;
        }
        $ids = collect([$dgaId, $assistanteId])->filter();
        return User::whereIn('id', $ids)->get();
    }

    // Rôles
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPca(): bool
    {
        return $this->role === 'PCA';
    }

    public function isDg(): bool
    {
        return $this->role === 'DG';
    }

    public function isAssistanteDg(): bool
    {
        return $this->role === 'Assistante_Dg';
    }

    public function isDga(): bool
    {
        return $this->role === 'DGA';
    }

    public function isSecretaireAssistante(): bool
    {
        return $this->role === 'Secretaire_Assistante';
    }

    public function isSecretaireDirection(): bool
    {
        return $this->role === 'Secretaire_Direction';
    }

    public function isSecretaireTechnique(): bool
    {
        return $this->role === 'Secretaire_Technique';
    }

    public function isSecretaireCaisse(): bool
    {
        return $this->role === 'Secretaire_Caisse';
    }

    public function isSecretaireAgence(): bool
    {
        return $this->role === 'Secretaire_Agence';
    }

    public function isConseillersDg(): bool
    {
        return $this->role === 'Conseillers_Dg';
    }

    public function isDirecteurDirection(): bool
    {
        return $this->role === 'Directeur_Direction';
    }

    public function isDirecteurCaisse(): bool
    {
        return $this->role === 'Directeur_Caisse';
    }

    public function isDirecteurTechnique(): bool
    {
        return $this->role === 'Directeur_Technique';
    }

    public function isChefService(): bool
    {
        return $this->role === 'Chef_Service';
    }

    public function isChefAgence(): bool
    {
        return $this->role === 'Chef_Agence';
    }

    public function isChefGuichet(): bool
    {
        return $this->role === 'Chef_Guichet';
    }

    public function isAgent(): bool
    {
        return $this->role === 'Agent';
    }

    public function isRh(): bool
    {
        return $this->role === 'RH';
    }

    public function isPersonnel(): bool
    {
        return in_array($this->role, [
            'PCA', 'DG', 'DGA', 'Assistante_Dg', 'Secretaire_Assistante', 'Conseillers_Dg',
            'Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse',
            'Secretaire_Direction', 'Secretaire_Technique', 'Secretaire_Caisse', 'Secretaire_Agence',
            'Chef_Agence', 'Chef_Guichet', 'Chef_Service',
            'RH', 'Agent',
        ], true);
    }
}

