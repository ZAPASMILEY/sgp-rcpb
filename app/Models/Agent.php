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
        'service_id',
        'nom',
        'prenom',
        'fonction',
        'numero_telephone',
        'email',
        'photo_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }

    public function evaluations(): MorphMany
    {
        return $this->morphMany(Evaluation::class, 'evaluable');
    }
}