<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            $table->foreignId('assigned_dajf_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->timestamp('assigned_dajf_at')->nullable()->after('assigned_dajf_id');

            $table->foreignId('assigned_dgu_id')->nullable()->constrained('users')->nullOnDelete()->after('assigned_dajf_at');
            $table->timestamp('assigned_dgu_at')->nullable()->after('assigned_dgu_id');

            $table->index(['type_projet','etat']);
            $table->index(['assigned_dajf_id']);
            $table->index(['assigned_dgu_id']);
        });
    }

    public function down(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_dajf_id');
            $table->dropColumn('assigned_dajf_at');
            $table->dropConstrainedForeignId('assigned_dgu_id');
            $table->dropColumn('assigned_dgu_at');
        });
    }
};
