<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme_preference',
        'role',
        'pca_entite_id',
        'sexe',
        'date_prise_fonction',
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

    // Relations
    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'pca_entite_id');
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
        return $this->role === 'Secretaire_assistante';
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
        return $this->role === 'Directeur_Tehnique';
    }

    public function isChefsService(): bool
    {
        return $this->role === 'Chefs de service';
    }

    public function isChefAgence(): bool
    {
        return $this->role === "chef d'agence";
    }

    public function isAgent(): bool
    {
        return $this->role === 'Agent';
    }

    public function isPersonnel(): bool
    {
        return in_array($this->role, [
            'PCA', 'DG', 'Assistante_Dg', 'DGA', 'Secretaire_assistante', 'Secretaire_Direction',
            'Secretaire_Technique', 'Secretaire_Caisse', 'Secretaire_Agence', 'Conseillers_Dg',
            'Directeur_Direction', 'Directeur_Caisse', 'Directeur_Tehnique', 'Chefs de service',
            "chef d'agence", 'Agent'
        ], true);
    }
}

