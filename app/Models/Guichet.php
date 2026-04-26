<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guichet extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'agence_id',
        // Responsable : FK vers agent
        'chef_agent_id',
    ];

    // ── Responsable ────────────────────────────────────────────────────────

    public function chef(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'chef_agent_id');
    }

    // ── Hiérarchie ─────────────────────────────────────────────────────────

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
