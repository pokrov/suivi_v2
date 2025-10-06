<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetitProjet extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identification / localisation
        'numero_dossier',
        'province',
        'commune_1',
        'commune_2',

        // Arrivée / registre
        'date_arrivee',
        'numero_arrivee',

        // Pétitionnaire / MO
        'petitionnaire',
        'a_proprietaire',     // bool
        'proprietaire',
        'categorie_petitionnaire',
        'maitre_oeuvre',

        // Projet
        'intitule_projet',
        'categorie_projet',   // array (JSON)
        'contexte_projet',
        'situation',
        'reference_fonciere',
        'lien_ged',
        'observations',

        // Surfaces / indicateurs (facultatifs, souvent utiles)
        'superficie_terrain',
        'superficie_couverte',
        'montant_investissement',
        'emplois_prevus',
        'nb_logements',

        // ROKHAS (avis saisi sur le champ)
        'rokhas_numero',          // ex: N° de la DP/PC dans Rokhas
        'rokhas_lien',            // lien direct (si utile)
        'rokhas_avis',            // favorable | defavorable | sous_reserve | sans_objet
        'rokhas_avis_date',
        'rokhas_avis_commentaire',
        'rokhas_piece_url',       // lien vers pièce jointe (pdf) si stockée ailleurs

        // Divers
        'user_id',                // créateur
        'etat',                   // 'enregistrement' | 'archive' (simple)
    ];

    protected $casts = [
        'categorie_projet'   => 'array',
        'a_proprietaire'     => 'boolean',
         'date_arrivee'     => 'date',
        'rokhas_avis_date'   => 'date',
    ];

    /* ===== Relations ===== */
    public function user() { return $this->belongsTo(\App\Models\User::class); }

    /* ===== Helpers ===== */
    public function isArchived(): bool { return $this->etat === 'archive'; }
}
