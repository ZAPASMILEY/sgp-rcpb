<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FicheObjectifObjectif extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiche_objectif_id',
        'description',
    ];

    public function ficheObjectif(): BelongsTo
    {
        return $this->belongsTo(FicheObjectif::class);
    }
}
