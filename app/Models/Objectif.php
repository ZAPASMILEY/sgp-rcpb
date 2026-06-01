<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objectif extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'assignable_type',
        'assignable_id',
        'annee_id',
        'date',
        'date_echeance',
        'commentaire',
        'avancement_percentage',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'avancement_percentage' => 'integer',
    ];

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function annee(): BelongsTo
    {
        return $this->belongsTo(Annee::class);
    }
}