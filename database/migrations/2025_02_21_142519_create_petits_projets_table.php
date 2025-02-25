<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('petits_projets', function (Blueprint $table) {
            $table->id();
            $table->string('numero_projet')->unique(); // Numéro de dossier
            $table->string('titre_projet'); // Titre ou nom du projet

            // Localisation
            $table->string('province'); // La province ou préfecture choisie
            $table->string('commune'); // Commune (sélection dynamique selon la province)

            // Commission : numéro et année
            $table->string('commission_numero')->nullable();
            $table->string('commission_annee')->nullable();

            // Avis de la commission
            $table->string('avis_commission')->nullable(); // Ex : favorable, défavorable, réexamen
            $table->string('numero_avis_favorable')->nullable(); // Saisie si avis favorable
            $table->text('motivation_avis')->nullable(); // Motivation de l'avis
            $table->text('observations')->nullable();

            // Informations sur le pétitionnaire et le projet
            $table->string('petitionnaire'); // Nom du pétitionnaire
            $table->string('categorie_petitionnaire')->nullable(); // Catégorie du pétitionnaire
            $table->string('categorie_projet')->nullable(); // Catégorie du projet
            $table->text('contexte')->nullable(); // Contexte du projet
            $table->string('maitre_oeuvre')->nullable(); // Maître d'œuvre
            $table->text('situation')->nullable(); // Situation du projet
            $table->string('reference_fonciere')->nullable(); // Référence foncière

            // Informations sur l'investissement et les mesures
            $table->decimal('surface_terrain', 10, 2)->nullable(); // Surface du terrain en m²
            $table->decimal('surface_batie', 10, 2)->nullable();  // Surface bâtie/couverte en m²
            $table->decimal('montant_investissement', 10, 2)->nullable(); // Montant de l'investissement
            $table->integer('nombre_logements')->nullable(); // Nombre de logements

            // Bouton pour voir le plan : lien vers un plan scanné
            $table->string('plan_url')->nullable();

            // Détails spécifiques pour la commission d'esthétique (sous forme de tableau)
            $table->json('commission_esthetique')->nullable();
            // Exemple de valeur JSON :
            // [{"numero": "001", "date": "2023-01-01", "examen": "Bon", "avis": "Favorable"}, ...]

            // Numéro de classement pour archivage
            $table->string('numero_classement')->nullable();

            // Statut du projet
            $table->enum('statut', ['en_saisie', 'en_validation', 'validé', 'rejeté', 'reexamen'])
                  ->default('en_saisie');

            // Clé étrangère vers l'utilisateur qui a créé le projet (facultatif)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('petits_projets');
    }
};
