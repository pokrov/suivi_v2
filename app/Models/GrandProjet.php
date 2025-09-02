<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrandProjet extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_projet',
        'numero_dossier',
        'province',
        'commune_1',
        'commune_2',
        'reference_envoi',
        'numero_envoi',
        'date_arrivee',
        'numero_arrivee',
        'date_commission_mixte',
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

        // ===== Complétion Bureau de suivi =====
        'date_commission_mixte_effective',
        'superficie_terrain',
        'superficie_couverte',
        'montant_investissement',
        'emplois_prevus',
        'nb_logements',
        'bs_completed_at',
        'bs_completed_by',
        'assigned_dajf_id','assigned_dajf_at',
    'assigned_dgu_id','assigned_dgu_at',
    ];

    /* ===== Relations ===== */
    public function user()        { return $this->belongsTo(User::class); }
    public function fluxEtapes()  { return $this->hasMany(FluxEtape::class)->orderByDesc('happened_at'); }
    public function examens()     { return $this->hasMany(\App\Models\Examen::class)->orderBy('numero_examen'); }
    public function lastExamen()  { return $this->hasOne(\App\Models\Examen::class)->latestOfMany('numero_examen'); }

    /* ===== Scopes ===== */
    public function scopeCpc($q)  { return $q->where('type_projet','cpc'); }
    public function scopeClm($q)  { return $q->where('type_projet','clm'); }
    public function scopeEtat($q, $etats)
    {
        $etats = is_array($etats) ? $etats : [$etats];
        return $q->whereIn('etat', $etats);
    }

    /* ===== Helpers ===== */
    public function isAtStage($stage){ return $this->etat === $stage; }

    public function getNextNumeroExamenAttribute(): int
    {
        return (int)($this->examens()->max('numero_examen') ?? 0) + 1;
    }
    public function assigneeDajf()
{
    return $this->belongsTo(User::class, 'assigned_dajf_id');
}
public function assigneeDgu()
{
    return $this->belongsTo(User::class, 'assigned_dgu_id');
}
    public function isFavorable(): bool
    {
        return optional($this->lastExamen)->avis === 'favorable';
    }

    /** éligible au bouton "Compléter" côté Chef/Saisie */
    public function canBeCompletedByBS(): bool
    {
        return $this->etat === 'retour_bs' ;
    }

    /* ===== Machine à états (mise à jour) ===== */
    public static function allowedMap(): array
    {
        return [
            'transmis_dajf'     => ['recu_dajf'],
            'recu_dajf'         => ['transmis_dgu'],
            'transmis_dgu'      => ['recu_dgu'],
            'recu_dgu'          => ['comm_interne'],
            'vers_comm_interne' => ['comm_interne'],
            'comm_interne'      => ['comm_mixte'],              // interne -> mixte (toujours)
            'comm_mixte'        => ['signature_3', 'retour_bs'],// mixte -> 3e sig OU retour_bs
            'signature_3'       => ['retour_bs','archive'],
            'retour_bs'         => ['archive'],
            'archive'           => [],
        ];
    }

    public function allowedTransitions(): array
    {
        $map = self::allowedMap();
        return $map[$this->etat] ?? [];
    }

    public static function roleTransitionMap(): array
    {
        return [
            'dajf' => [
                'transmis_dajf'  => ['recu_dajf'],
                'recu_dajf'      => ['transmis_dgu'],
            ],
            'dgu' => [
                'transmis_dgu'   => ['recu_dgu'],
                'recu_dgu'       => ['comm_interne'],
            ],
            // Commission interne -> mixte gérée par ExamenController
        ];
    }

    /* ===== Ordre du tracker (affichage) ===== */
    public static function trackerSteps(): array
    {
        return [
            'transmis_dajf',     // 1  Saisie (libellé UI)
            'recu_dajf',         // 2  Vers DAJF
            'transmis_dgu',      // 3  DAJF
            'recu_dgu',          // 4  Vers DGU
            'vers_comm_interne', // 5  DGU (alias UI)
            'comm_interne',      // 6  Vers Comm. Interne
            'comm_mixte',        // 7  Comm. Interne
            'signature_3',       // 8  Comm. Mixte
            'retour_bs',         // 9  3ᵉ signature (puis retour_bs)
            'archive',           // 10 Bureau de suivi / 11 Archivé (affichage en vue)
        ];
    }

    public function trackerPosition(): int
    {
        $steps = self::trackerSteps();
        $idx   = array_search($this->etat, $steps, true);
        return $idx === false ? 0 : $idx;
    }
    protected $casts = [
    'categorie_projet' => 'array',
    'envoi_papier'     => 'boolean',
    
];

}
