<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ville extends Model
{
    protected $fillable = [
        'delegation_technique_id',
        'nom',
    ];

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }
}
