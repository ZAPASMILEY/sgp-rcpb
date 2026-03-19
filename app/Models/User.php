<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme_preference',
        'role',
        'pca_entite_id',
    ];

    // Roles valides: admin, pca, agent, directeur, directeur_adjoint, assistant, chef, secretaire

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluateur_id');
    }

    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class, 'pca_entite_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPca(): bool
    {
        return $this->role === 'pca';
    }

    public function isPersonnel(): bool
    {
        return in_array($this->role, ['agent', 'directeur', 'directeur_adjoint', 'assistant', 'chef', 'secretaire'], true);
    }
}
