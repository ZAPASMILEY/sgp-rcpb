<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entite;

class FixDirectionGeneraleEntite extends Command
{
    protected $signature = 'fix:direction-generale-entite';
    protected $description = "Force l'entité 'Direction Générale' à avoir parent_id = NULL (faîtière)";

    public function handle()
    {
        $entite = Entite::where('nom', 'Direction Générale')->first();
        if (!$entite) {
            $this->error("Aucune entité 'Direction Générale' trouvée.");
            return 1;
        }
        $entite->parent_id = null;
        $entite->save();
        $this->info("L'entité 'Direction Générale' est maintenant faîtière (parent_id = NULL).");
        return 0;
    }
}
