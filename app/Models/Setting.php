<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // ── Cache en mémoire pour éviter les N+1 dans la même requête ────────────
    private static array $cache = [];

    /**
     * Lit une valeur depuis la table settings.
     * Retourne $default si la clé n'existe pas.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        }

        $setting = static::where('key', $key)->first();
        $value   = $setting ? $setting->value : $default;

        static::$cache[$key] = $value;

        return $value;
    }

    /**
     * Écrit (crée ou met à jour) une valeur.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);

        // Invalide le cache en mémoire pour cette clé
        static::$cache[$key] = (string) $value;
    }

    /**
     * Retourne true si la fonctionnalité est activée.
     * Les fonctionnalités sont activées par défaut si la clé est absente.
     */
    public static function featureEnabled(string $feature): bool
    {
        return static::get($feature . '_enabled', '1') === '1';
    }
}
