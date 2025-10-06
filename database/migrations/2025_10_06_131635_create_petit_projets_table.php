<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('petit_projets', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('numero_dossier')->index();
            $table->string('province')->nullable();
            $table->string('commune_1')->nullable();
            $table->string('commune_2')->nullable();

            // Arrivée
            $table->date('date_arrivee')->nullable();
            $table->string('numero_arrivee')->nullable();

            // Acteurs
            $table->string('petitionnaire')->nullable();
            $table->boolean('a_proprietaire')->default(false);
            $table->string('proprietaire')->nullable();
            $table->string('categorie_petitionnaire')->nullable();
            $table->string('maitre_oeuvre')->nullable();

            // Projet
            $table->string('intitule_projet')->nullable();
            $table->json('categorie_projet')->nullable();
            $table->string('contexte_projet')->nullable();
            $table->string('situation')->nullable();
            $table->string('reference_fonciere')->nullable();
            $table->string('lien_ged')->nullable();
            $table->text('observations')->nullable();

            // Indicateurs
            $table->decimal('superficie_terrain', 12, 2)->nullable();
            $table->decimal('superficie_couverte', 12, 2)->nullable();
            $table->decimal('montant_investissement', 14, 2)->nullable();
            $table->unsignedInteger('emplois_prevus')->nullable();
            $table->unsignedInteger('nb_logements')->nullable();

            // Rokhas
            $table->string('rokhas_numero')->nullable();
            $table->string('rokhas_lien')->nullable();
            $table->enum('rokhas_avis', ['favorable','defavorable','sous_reserve','sans_objet'])->nullable();
            $table->date('rokhas_avis_date')->nullable();
            $table->text('rokhas_avis_commentaire')->nullable();
            $table->string('rokhas_piece_url')->nullable();

            // Divers
            $table->string('etat')->default('enregistrement')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            // Option : éviter les doublons stricts sur le numéro
            // $table->unique('numero_dossier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petit_projets');
    }
};
