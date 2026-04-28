<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneFicheObjectif extends Model
{
    use HasFactory;

    protected $table = 'lignes_fiche_objectif';

    protected $fillable = [
        'fiche_objectif_id',
        'description',
        'note_obtenue',
    ];

    protected $casts = [
        'note_obtenue' => 'decimal:2',
    ];

    public function ficheObjectif(): BelongsTo
    {
        return $this->belongsTo(FicheObjectif::class);
    }
}
