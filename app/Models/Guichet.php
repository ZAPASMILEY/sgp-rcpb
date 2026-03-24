<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guichet extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'chef_nom',
        'chef_email',
        'chef_telephone',
        'agence_id',
    ];

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }
}
