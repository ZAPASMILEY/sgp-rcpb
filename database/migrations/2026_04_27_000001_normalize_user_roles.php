<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalise les valeurs de la colonne users.role.
 *
 * Corrections appliquées :
 *  - 'Directeur_Tehnique'   → 'Directeur_Technique'  (faute de frappe)
 *  - 'Chefs de service'     → 'Chef_Service'          (espaces → underscore)
 *  - "chef d'agence"        → 'Chef_Agence'           (minuscule + apostrophe → PascalCase)
 *  - 'Secretaire_assistante'→ 'Secretaire_Assistante' (cohérence casse)
 */
return new class extends Migration
{
    private const RENAMES = [
        'Directeur_Tehnique'    => 'Directeur_Technique',
        'Chefs de service'      => 'Chef_Service',
        "chef d'agence"         => 'Chef_Agence',
        'Secretaire_assistante' => 'Secretaire_Assistante',
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $old => $new) {
            DB::table('users')
                ->where('role', $old)
                ->update(['role' => $new]);
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $old => $new) {
            DB::table('users')
                ->where('role', $new)
                ->update(['role' => $old]);
        }
    }
};
