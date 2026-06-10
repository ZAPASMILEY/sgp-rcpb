<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'type',
        'priorite',
        'titre',
        'message',
        'statut',
        'ip_address',
        'created_by',
        'lien',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function destinataires()
    {
        return $this->belongsToMany(User::class, 'alerte_user')
            ->withPivot('lu', 'lu_at')
            ->withTimestamps();
    }

    /**
     * Diffuser l'alerte à tous les utilisateurs.
     */
    public function diffuserATous(): void
    {
        $userIds = User::pluck('id');
        $this->destinataires()->syncWithoutDetaching(
            $userIds->mapWithKeys(fn ($id) => [$id => ['lu' => false]])->all()
        );
    }

    /**
     * Créer une notification système et l'envoyer à un utilisateur précis.
     */
    public static function notifier(int $userId, string $titre, string $message = '', string $priorite = 'moyenne', ?string $lien = null): void
    {
        // Normaliser le lien : toujours stocker un chemin relatif
        // pour éviter les redirections vers 127.0.0.1 ou un domaine périmé
        if ($lien !== null) {
            $parsed = parse_url($lien);
            $lien   = ($parsed['path'] ?? '/')
                . (isset($parsed['query'])    ? '?' . $parsed['query']    : '')
                . (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');
        }

        $alerte = static::create([
            'type'       => 'systeme',
            'priorite'   => $priorite,
            'titre'      => $titre,
            'message'    => $message,
            'statut'     => 'active',
            'created_by' => null,
            'lien'       => $lien,
        ]);
        $alerte->destinataires()->attach($userId, ['lu' => false]);
    }
}
