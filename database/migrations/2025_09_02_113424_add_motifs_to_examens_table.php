<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->json('motifs')->nullable()->after('avis');
            $table->string('motif_autre')->nullable()->after('motifs');
        });
    }

    public function down(): void
    {
        Schema::table('examens', function (Blueprint $table) {
            $table->dropColumn(['motifs','motif_autre']);
        });
    }
};
