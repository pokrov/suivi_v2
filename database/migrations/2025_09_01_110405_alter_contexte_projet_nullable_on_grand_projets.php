<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            $table->string('contexte_projet', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('grand_projets', function (Blueprint $table) {
            $table->string('contexte_projet', 120)->nullable(false)->default('')->change();
        });
    }
};
