<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Passe 'etat' en VARCHAR(32) pour accepter les nouveaux états (ex: vers_comm_interne)
        DB::statement("ALTER TABLE grand_projets MODIFY etat VARCHAR(32) NOT NULL DEFAULT 'transmis_dajf'");
    }

    public function down(): void
    {
        // Revenir sans défaut explicite (garde VARCHAR pour éviter de recréer un ENUM fragile)
        DB::statement("ALTER TABLE grand_projets MODIFY etat VARCHAR(32) NOT NULL");
    }
};
