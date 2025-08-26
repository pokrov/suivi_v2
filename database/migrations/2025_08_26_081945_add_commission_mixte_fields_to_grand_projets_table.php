<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            if (!Schema::hasColumn('grand_projets', 'date_commission_mixte')) {
                $table->date('date_commission_mixte')->nullable()->after('date_commission_interne');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            if (Schema::hasColumn('grand_projets', 'date_commission_mixte')) {
                $table->dropColumn('date_commission_mixte');
            }
        });
    }
};
