<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectiveSubcriteriaTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'subjective_criteria_template_id',
        'ordre',
        'libelle',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SubjectiveCriteriaTemplate::class, 'subjective_criteria_template_id');
    }
}
