<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('examens', function (Blueprint $table) {
            // 1) Supprimer l'ancienne colonne si elle existe encore
            if (Schema::hasColumn('examens', 'date_commission')) {
                $table->dropColumn('date_commission');
            }

            // 2) Ajouter le champ type_examen si absent
            if (!Schema::hasColumn('examens', 'type_examen')) {
                $table->string('type_examen')->default('interne'); 
                // ex: interne, mixte, signature
            }

            // 3) S'assurer que date_examen existe
            if (!Schema::hasColumn('examens', 'date_examen')) {
                $table->date('date_examen')->nullable()->after('numero_examen');
            }
        });
    }

    public function down(): void {
        Schema::table('examens', function (Blueprint $table) {
            if (Schema::hasColumn('examens', 'date_examen')) {
                $table->dropColumn('date_examen');
            }
            if (Schema::hasColumn('examens', 'type_examen')) {
                $table->dropColumn('type_examen');
            }
            // remettre l’ancienne colonne
            if (!Schema::hasColumn('examens', 'date_commission')) {
                $table->date('date_commission')->nullable();
            }
        });
    }
};
