<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Annee extends Model
{
    use HasFactory, Auditable;

    /** @var list<string> */
    protected $fillable = [
        'annee',
        'statut',
    ];

    public function objectifs(): HasMany
    {
        return $this->hasMany(Objectif::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function semestres(): HasMany
    {
        return $this->hasMany(Semestre::class)->orderBy('numero');
    }

    /**
     * Crée les deux semestres (S1 et S2) pour cette année s'ils n'existent pas encore.
     */
    public function createSemestresIfMissing(): void
    {
        foreach ([1, 2] as $numero) {
            $this->semestres()->firstOrCreate(
                ['numero' => $numero],
                ['statut' => 'cloture']
            );
        }
    }

    /**
     * Vérifie si un semestre donné (1 ou 2) est ouvert pour cette année.
     */
    public function isSemestreOuvert(int $numero): bool
    {
        return $this->semestres()->where('numero', $numero)->where('statut', 'ouvert')->exists();
    }

    public static function resolveIdForDate(CarbonInterface|string $date): int
    {
        $year = (int) Carbon::parse($date)->year;

        return (int) static::query()->firstOrCreate([
            'annee' => $year,
        ])->id;
    }

    /**
     * Retourne l'année actuellement ouverte, ou null si aucune.
     */
    public static function currentOpen(): ?static
    {
        return static::where('statut', 'ouvert')->orderByDesc('annee')->first();
    }

    /**
     * Indique si au moins une année est ouverte.
     */
    public static function hasOpenYear(): bool
    {
        return static::where('statut', 'ouvert')->exists();
    }

    /**
     * Comme resolveIdForDate, mais vérifie que l'année de la date est ouverte.
     * Lance une \RuntimeException si l'année est clôturée ou si aucune année n'est ouverte.
     */
    public static function resolveOpenYearId(CarbonInterface|string $date): int
    {
        $year      = (int) Carbon::parse($date)->year;
        $openAnnee = static::where('statut', 'ouvert')->orderByDesc('annee')->first();

        if (! $openAnnee) {
            throw new \RuntimeException("Aucune année d'exercice ouverte. Contactez l'administrateur.");
        }

        if ($openAnnee->annee !== $year) {
            throw new \RuntimeException(
                "Création impossible : seule l'année {$openAnnee->annee} est ouverte. La date saisie appartient à l'année {$year}."
            );
        }

        return (int) $openAnnee->id;
    }

    /**
     * Résout l'ID du semestre ouvert correspondant à la date donnée.
     * Lance une \RuntimeException si l'année ou le semestre est clôturé.
     *
     * @throws \RuntimeException
     */
    public static function resolveOpenSemestreId(CarbonInterface|string $date): int
    {
        $parsed  = Carbon::parse($date);
        $anneeId = static::resolveOpenYearId($parsed); // valide l'année
        $numero  = $parsed->month <= 6 ? 1 : 2;

        $semestre = Semestre::where('annee_id', $anneeId)
            ->where('numero', $numero)
            ->first();

        if (! $semestre || $semestre->statut !== 'ouvert') {
            throw new \RuntimeException(
                "Le semestre {$numero} de l'exercice n'est pas encore ouvert. Contactez l'administrateur."
            );
        }

        return (int) $semestre->id;
    }
}