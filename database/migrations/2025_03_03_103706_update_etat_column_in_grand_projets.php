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
        $table->enum('etat', [
            'enregistrement',
            'transmis_dajf',
            'recu_dajf',
            'transmis_dgu',
            'recu_dgu',
            'comm_interne',
            'retour_dgu',
            'retour_bs',
            'archive'
        ])->default('enregistrement')->change();
    });
}

public function down()
{
    Schema::table('grand_projets', function (Blueprint $table) {
        $table->string('etat')->change(); // Remettre en texte simple en cas de rollback
    });
}

};
