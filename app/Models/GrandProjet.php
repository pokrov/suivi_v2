<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrandProjet extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     */
    protected $fillable = [
        'type_projet', 
        'numero_dossier', 
        'province', 
        'commune_1', 
        'commune_2',
        'reference_envoi', 
        'numero_envoi', 
        'date_arrivee', 
        'date_commission_interne',
        'petitionnaire', 
        'a_proprietaire', 
        'proprietaire', 
        'categorie_petitionnaire',
        'intitule_projet', 
        'lien_ged', 
        'categorie_projet', 
        'contexte_projet',
        'maitre_oeuvre', 
        'situation', 
        'reference_fonciere', 
        'observations', 
        'etat', 
        'user_id',
    ];

    /**
     * Relation : chaque grand projet appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer les projets de type CPC.
     */
    public function scopeCpc($query)
    {
        return $query->where('type_projet', 'cpc');
    }

    /**
     * Scope pour filtrer les projets de type CLM.
     */
    public function scopeClm($query)
    {
        return $query->where('type_projet', 'clm');
    }
}
