<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectiveCriteriaTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordre',
        'titre',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subcriteria(): HasMany
    {
        return $this->hasMany(SubjectiveSubcriteriaTemplate::class)->orderBy('ordre');
    }
}
