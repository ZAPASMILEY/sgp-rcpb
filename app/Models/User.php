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

    // Subordonnés du DG (DGA, secrétaire)
    public function subordonnes()
    {
        $entite = $this->entite;
        if (!$entite) {
            return collect();
        }
        $dgaId = $entite->dga_user_id ?? null;
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
        return $this->role === 'pca';
    }

    public function isDg(): bool
    {
        return $this->role === 'dg';
    }

    public function isDga(): bool
    {
        return $this->role === 'dga';
    }

    public function isPersonnel(): bool
    {
        return in_array($this->role, [
            'agent', 'directeur', 'directeur_adjoint', 'assistante', 'chef', 'secretaire', 'pca', 'rh', 'dg', 'dga'
        ], true);
    }
}

