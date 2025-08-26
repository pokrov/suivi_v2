<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('grand_projets', function (Blueprint $table) {
        $table->string('numero_arrivee')->nullable()->after('numero_envoi');
    });
}

public function down()
{
    Schema::table('grand_projets', function (Blueprint $table) {
        $table->dropColumn('numero_arrivee');
    });
}

};
