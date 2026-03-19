<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agence extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'chef_nom',
        'chef_email',
        'chef_telephone',
        'secretaire_nom',
        'secretaire_email',
        'secretaire_telephone',
        'delegation_technique_id',
        'superviseur_caisse_id',
    ];

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    public function superviseurCaisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class, 'superviseur_caisse_id');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
