<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            // Champs spécifiques CLM
            $table->decimal('superficie_morcelee', 14, 2)->nullable()->after('superficie_couverte');
            $table->decimal('superficie_lotie', 14, 2)->nullable()->after('superficie_morcelee');
            $table->text('consistance')->nullable()->after('superficie_lotie');
        });
    }

    public function down(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            $table->dropColumn(['superficie_morcelee','superficie_lotie','consistance']);
        });
    }
};
