<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DelegationTechnique extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'region',
        'ville',
        'secretariat_telephone',
    ];

    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class);
    }

    public function services(): HasManyThrough
    {
        return $this->hasManyThrough(Service::class, Direction::class, 'delegation_technique_id', 'direction_id');
    }
}
