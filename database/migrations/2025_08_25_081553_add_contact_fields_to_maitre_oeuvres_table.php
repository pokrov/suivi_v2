<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('maitre_oeuvres', function (Blueprint $table) {
            // Ajoute seulement si absent (idempotent en dev)
            if (!Schema::hasColumn('maitre_oeuvres', 'email')) {
                $table->string('email')->nullable()->after('nom');
            }
            if (!Schema::hasColumn('maitre_oeuvres', 'telephone')) {
                $table->string('telephone', 50)->nullable()->after('email');
            }
            if (!Schema::hasColumn('maitre_oeuvres', 'adresse')) {
                $table->string('adresse')->nullable()->after('telephone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('maitre_oeuvres', function (Blueprint $table) {
            if (Schema::hasColumn('maitre_oeuvres', 'adresse')) {
                $table->dropColumn('adresse');
            }
            if (Schema::hasColumn('maitre_oeuvres', 'telephone')) {
                $table->dropColumn('telephone');
            }
            if (Schema::hasColumn('maitre_oeuvres', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
