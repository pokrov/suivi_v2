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
    public function isAtStage($stage)
{
    return $this->etat === $stage;
}
// (1) Relations navette + examens
public function fluxEtapes()
{
    return $this->hasMany(\App\Models\FluxEtape::class)->orderByDesc('happened_at');
}

public function examens()
{
    return $this->hasMany(\App\Models\Examen::class)->orderBy('numero_examen');
}

public function lastExamen()
{
    return $this->hasOne(\App\Models\Examen::class)->latestOfMany('numero_examen');
}

// (2) Aides examens
public function getNextNumeroExamenAttribute(): int
{
    return (int)($this->examens()->max('numero_examen') ?? 0) + 1;
}

public function isFavorable(): bool
{
    return optional($this->lastExamen)->avis === 'favorable';
}

// (3) Machine à états : transitions autorisées
public static function allowedMap(): array
{
    return [
        'enregistrement' => ['transmis_dajf'],
        'transmis_dajf'  => ['recu_dajf'],
        'recu_dajf'      => ['transmis_dgu'],
        'transmis_dgu'   => ['recu_dgu'],
        'recu_dgu'       => ['comm_interne'],
        'comm_interne'   => ['retour_dgu','retour_bs'], // après avis commission
        'retour_dgu'     => ['transmis_dgu','archive'], // boucle possible
        'retour_bs'      => ['archive'],
        'archive'        => [], // terminal
    ];
}

public function allowedTransitions(): array
{
    $map = self::allowedMap();
    return $map[$this->etat] ?? [];
}

// Filtrer par état (utile dans les dashboards)
public function scopeEtat($query, $etats)
{
    $etats = is_array($etats) ? $etats : [$etats];
    return $query->whereIn('etat', $etats);
}

/**
 * Transitions autorisées PAR RÔLE (contrôle d'accès fonctionnel)
 * - DAJF :   transmis_dajf -> recu_dajf ; recu_dajf -> transmis_dgu
 * - DGU :    transmis_dgu  -> recu_dgu  ; recu_dgu  -> comm_interne
 * - COMM :   avis via ExamenController (on ne change pas directement l'état ici)
 */
public static function roleTransitionMap(): array
{
    return [
        'dajf' => [
            'transmis_dajf' => ['recu_dajf'],
            'recu_dajf'     => ['transmis_dgu'],
        ],
        'dgu' => [
            'transmis_dgu'  => ['recu_dgu'],
            'recu_dgu'      => ['comm_interne'],
        ],
        // commission_interne : transitions via ExamenController (rediriger_vers retour_dgu/retour_bs)
    ];
}



}
