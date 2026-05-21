<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poste extends Model
{
    protected $fillable = ['fonction', 'libelle'];

    /**
     * Retourne tous les libellés indexés par fonction.
     *
     * @return array<string, list<string>>
     */
    public static function byFonction(): array
    {
        return static::orderBy('libelle')
            ->get()
            ->groupBy('fonction')
            ->map(fn ($items) => $items->pluck('libelle')->values()->all())
            ->all();
    }
}
