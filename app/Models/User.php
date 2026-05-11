<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough; // Import indispensable
use App\Models\Agent;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'agent_id',
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'must_change_password',
        'is_active',
        'theme_preference',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * RELATION MANQUANTE : Permet d'accéder à l'entité du PCA/DG via l'agent
     */
    public function entite(): HasOneThrough
    {
        return $this->hasOneThrough(
            Entite::class,
            Agent::class,
            'id',           // Clé étrangère sur agents (id de l'agent)
            'id',           // Clé étrangère sur entites (id de l'entité)
            'agent_id',     // Clé locale sur users
            'entite_id'     // Clé locale sur agents
        );
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

    public function activites(): HasMany
    {
        return $this->hasMany(Activite::class);
    }

    // ── Logique Métier ───────────────────────────────────────────────────────

    public function subordonnes()
    {
        // Maintenant $this->entite fonctionnera car la relation est définie au-dessus
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

    // ── Rôles ─────────────────────────────────────────────────────────────────

    public function isAdmin(): bool { return $this->role === 'Admin'; }
    public function isPca(): bool { return $this->role === 'PCA'; }
    public function isDg(): bool { return $this->role === 'DG'; }
    public function isAssistanteDg(): bool { return $this->role === 'Assistante_Dg'; }
    public function isDga(): bool { return $this->role === 'DGA'; }
    public function isSecretaireAssistante(): bool { return $this->role === 'Secretaire_Assistante'; }
    public function isSecretaireDirection(): bool { return $this->role === 'Secretaire_Direction'; }
    public function isSecretaireTechnique(): bool { return $this->role === 'Secretaire_Technique'; }
    public function isSecretaireCaisse(): bool { return $this->role === 'Secretaire_Caisse'; }
    public function isSecretaireAgence(): bool { return $this->role === 'Secretaire_Agence'; }
    public function isConseillersDg(): bool { return $this->role === 'Conseillers_Dg'; }
    public function isDirecteurDirection(): bool { return $this->role === 'Directeur_Direction'; }
    public function isDirecteurCaisse(): bool { return $this->role === 'Directeur_Caisse'; }
    public function isDirecteurTechnique(): bool { return $this->role === 'Directeur_Technique'; }
    public function isChefService(): bool { return $this->role === 'Chef_Service'; }
    public function isChefAgence(): bool { return $this->role === 'Chef_Agence'; }
    public function isChefGuichet(): bool { return $this->role === 'Chef_Guichet'; }
    public function isAgent(): bool { return $this->role === 'Agent'; }
    public function isRh(): bool { return $this->role === 'RH'; }

    public function isPersonnel(): bool
    {
        return in_array($this->role, [
            'PCA', 'DG', 'DGA', 'Assistante_Dg', 'Secretaire_Assistante', 'Conseillers_Dg',
            'Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse',
            'Secretaire_Direction', 'Secretaire_Technique', 'Secretaire_Caisse', 'Secretaire_Agence',
            'Chef_Agence', 'Chef_Guichet', 'Chef_Service',
            'Agent',
        ], true);
    }
}