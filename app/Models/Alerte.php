<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'priorite',
        'titre',
        'message',
        'statut',
        'ip_address',
        'created_by',
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
    public static function notifier(int $userId, string $titre, string $message, string $priorite = 'moyenne'): void
    {
        $alerte = static::create([
            'type'       => 'systeme',
            'priorite'   => $priorite,
            'titre'      => $titre,
            'message'    => $message,
            'statut'     => 'active',
            'created_by' => null,
        ]);
        $alerte->destinataires()->attach($userId, ['lu' => false]);
    }
}
