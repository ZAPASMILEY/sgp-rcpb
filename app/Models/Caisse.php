<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caisse extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'directeur_nom',
        'directeur_email',
        'directeur_telephone',
        'secretariat_telephone',
        'superviseur_direction_id',
    ];

    public function superviseur(): BelongsTo
    {
        return $this->belongsTo(Direction::class, 'superviseur_direction_id');
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class, 'superviseur_caisse_id');
    }
}
