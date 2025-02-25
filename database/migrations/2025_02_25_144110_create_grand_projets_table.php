<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('grand_projets', function (Blueprint $table) {
            $table->id();
            // Type de projet : 'cpc' pour projet de construction, 'clm' pour Lotissement/Morcellement
            $table->enum('type_projet', ['cpc', 'clm'])->comment('Type de projet : cpc ou clm');
            $table->string('numero_dossier')->comment('Numéro de dossier sous la forme textnumérique/année');
            $table->string('province')->default('Préfecture Oujda-Angad')->comment('Province ou Préfecture');
            $table->string('commune_1')->comment('Première commune');
            $table->string('commune_2')->nullable()->comment('Deuxième commune, optionnelle');
            $table->string('reference_envoi')->nullable()->comment('Référence d\'envoi (utilisé pour certains cas)');
            $table->string('numero_envoi')->nullable()->comment('Numéro d\'envoi (optionnel)');
            $table->date('date_arrivee')->comment('Date d\'arrivée du dossier');
            $table->date('date_commission_interne')->nullable()->comment('Date de commission interne (provisoire)');
            $table->string('petitionnaire')->comment('Nom du pétitionnaire');
            $table->boolean('a_proprietaire')->default(false)->comment('Indique si un propriétaire est renseigné');
            $table->string('proprietaire')->nullable()->comment('Nom du propriétaire si applicable');
            $table->string('categorie_petitionnaire')->comment('Catégorie du pétitionnaire');
            $table->string('intitule_projet')->comment('Intitulé du projet');
            $table->string('lien_ged')->nullable()->comment('Lien vers la GED');
            $table->string('categorie_projet')->comment('Catégorie du projet (ex: CPC, CLM)');
            $table->string('contexte_projet')->comment('Contexte du projet');
            $table->string('maitre_oeuvre')->comment('Nom du maître d\'œuvre');
            $table->string('situation')->comment('Adresse ou situation du projet');
            $table->string('reference_fonciere')->comment('Références foncières');
            $table->text('observations')->nullable()->comment('Observations complémentaires');
            $table->enum('etat', ['enregistrement', 'completer', 'facturation', 'archive', 'reexamen'])
                  ->default('enregistrement')
                  ->comment('État actuel du projet');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Utilisateur ayant saisi le projet');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('grand_projets');
    }
};
