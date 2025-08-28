<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            // 1. Date réelle de commission mixte (corrigée)
            $table->date('date_commission_mixte_effective')->nullable()->after('date_commission_mixte');

            // 3. Superficies
            $table->decimal('superficie_terrain', 12, 2)->nullable();   // m²
            $table->decimal('superficie_couverte', 12, 2)->nullable();  // m² (clé facturation)

            // 5. Montant d’investissement (MAD)
            $table->decimal('montant_investissement', 14, 2)->nullable();

            // 6-7. Emplois + logements
            $table->unsignedInteger('emplois_prevus')->nullable();
            $table->unsignedInteger('nb_logements')->nullable();

            // Statut de complétion Bureau de suivi
            $table->timestamp('bs_completed_at')->nullable();
            $table->unsignedBigInteger('bs_completed_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            $table->dropColumn([
                'date_commission_mixte_effective',
                'superficie_terrain',
                'superficie_couverte',
                'montant_investissement',
                'emplois_prevus',
                'nb_logements',
                'bs_completed_at',
                'bs_completed_by',
            ]);
        });
    }
};
