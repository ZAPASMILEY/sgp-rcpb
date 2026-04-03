<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\User;

class Agent extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'delegation_technique_id',
        'service_id',
        'agence_id',
        'nom',
        'prenom',
        'sexe',
        'fonction',
        'date_debut_fonction',
        'numero_telephone',
        'email',
        'photo_path',
    ];

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }

    public function evaluations(): MorphMany
    {
        return $this->morphMany(Evaluation::class, 'evaluable');
    }

    protected function casts(): array
    {
        return [
            'date_debut_fonction' => 'date',
        ];
    }
}
