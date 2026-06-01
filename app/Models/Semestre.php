<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semestre extends Model
{
    protected $fillable = ['annee_id', 'numero', 'statut'];

    public function annee(): BelongsTo
    {
        return $this->belongsTo(Annee::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Retourne le semestre actuellement ouvert, ou null si aucun.
     */
    public static function currentOpen(): ?static
    {
        return static::where('statut', 'ouvert')
            ->whereHas('annee', fn ($q) => $q->where('statut', 'ouvert'))
            ->orderByDesc('annee_id')
            ->orderBy('numero')
            ->first();
    }

    /**
     * Retourne tous les semestres ouverts (peut y en avoir 0, 1 ou 2).
     */
    public static function allOpen(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('statut', 'ouvert')
            ->whereHas('annee', fn ($q) => $q->where('statut', 'ouvert'))
            ->with('annee')
            ->orderByDesc('annee_id')
            ->orderBy('numero')
            ->get();
    }

    /**
     * Indique si le semestre N de l'année donnée est ouvert.
     */
    public static function isOpen(int $anneeId, int $numero): bool
    {
        return static::where('annee_id', $anneeId)
            ->where('numero', $numero)
            ->where('statut', 'ouvert')
            ->exists();
    }

    public function label(): Attribute
    {
        return Attribute::make(
            get: fn () => "Semestre {$this->numero}" . ($this->annee ? " — {$this->annee->annee}" : ''),
        );
    }

    /** Date de début du semestre (1er jan ou 1er juil) */
    public function dateDebut(): Carbon
    {
        $mois = $this->numero === 1 ? 1 : 7;
        return Carbon::create($this->annee->annee, $mois, 1);
    }

    /** Date de fin du semestre (30 juin ou 31 déc) */
    public function dateFin(): Carbon
    {
        $mois = $this->numero === 1 ? 6 : 12;
        return Carbon::create($this->annee->annee, $mois, 1)->endOfMonth();
    }

    /** Libellé de la période ex: "01/01/2026 — 30/06/2026" */
    public function periodeLabel(): string
    {
        return $this->dateDebut()->format('d/m/Y').' — '.$this->dateFin()->format('d/m/Y');
    }
}
