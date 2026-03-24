<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Annee extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'annee',
    ];

    public function objectifs(): HasMany
    {
        return $this->hasMany(Objectif::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public static function resolveIdForDate(CarbonInterface|string $date): int
    {
        $year = (int) Carbon::parse($date)->year;

        return (int) static::query()->firstOrCreate([
            'annee' => $year,
        ])->id;
    }
}