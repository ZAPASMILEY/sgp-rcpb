<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomRole extends Model
{
    protected $fillable = ['slug', 'label'];

    /** Retourne tous les rôles custom sous forme slug => label. */
    public static function allAsMap(): array
    {
        return static::orderBy('label')->pluck('label', 'slug')->toArray();
    }
}
