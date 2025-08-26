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
        // si tu as cette colonne
     
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

    public function isFavorable(): bool
    {
        return optional($this->lastExamen)->avis === 'favorable';
    }

    /* ===== Machine à états (complète) ===== */
    public static function allowedMap(): array
    {
        return [
            'enregistrement' => ['transmis_dajf','recu_dajf'], // selon ton flux réel
            'transmis_dajf'  => ['recu_dajf'],
            'recu_dajf'      => ['transmis_dgu'],
            'transmis_dgu'   => ['recu_dgu'],
            'recu_dgu'       => ['comm_interne'],
            'comm_interne'   => ['comm_mixte','signature_3','retour_bs','retour_dgu'],
            'comm_mixte'     => ['signature_3','retour_bs'],
            'signature_3'    => ['retour_bs','archive'],
            'retour_dgu'     => ['transmis_dgu','archive'],
            'retour_bs'      => ['archive'],
            'archive'        => [],
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
                'enregistrement' => ['recu_dajf'],
                'transmis_dajf'  => ['recu_dajf'],
                'recu_dajf'      => ['transmis_dgu'],
            ],
            'dgu' => [
                'transmis_dgu'  => ['recu_dgu'],
                'recu_dgu'      => ['comm_interne'],
            ],
            // commission interne via ExamenController → comm_mixte ou autre
        ];
    }

    /* ===== Ordre du tracker (affichage) ===== */
    public static function trackerSteps(): array
    {
        return [
            'enregistrement',
            'transmis_dajf',
            'recu_dajf',
            'transmis_dgu',
            'recu_dgu',
            'comm_interne',
            'comm_mixte',
            'signature_3',
            'retour_bs',
            'archive',
        ];
    }

    public function trackerPosition(): int
    {
        $steps = self::trackerSteps();
        $idx   = array_search($this->etat, $steps, true);
        return $idx === false ? 0 : $idx;
    }
}
