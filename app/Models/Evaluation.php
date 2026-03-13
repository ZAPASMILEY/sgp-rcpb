<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Evaluation extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'evaluable_type',
        'evaluable_id',
        'evaluateur_id',
        'date_debut',
        'date_fin',
        'note_objectifs',
        'note_manuelle',
        'note_finale',
        'commentaire',
        'statut',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'note_objectifs' => 'integer',
        'note_manuelle'  => 'integer',
        'note_finale'    => 'integer',
        'date_debut'     => 'date',
        'date_fin'       => 'date',
    ];

    public function evaluable(): MorphTo
    {
        return $this->morphTo();
    }

    public function evaluateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }
}
