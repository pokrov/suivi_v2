<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetitProjet extends Model
{
    use HasFactory;

    protected $table = 'petits_projets';

    protected $fillable = [
        'numero_projet',
        'titre_projet',
        'province',
        'commune',
        'commission_numero',
        'commission_annee',
        'avis_commission',
        'numero_avis_favorable',
        'motivation_avis',
        'observations',
        'petitionnaire',
        'categorie_petitionnaire',
        'categorie_projet',
        'contexte',
        'maitre_oeuvre',
        'situation',
        'reference_fonciere',
        'surface_terrain',
        'surface_batie',
        'montant_investissement',
        'nombre_logements',
        'plan_url',
        'commission_esthetique',
        'numero_classement',
        'statut',
        'user_id',
    ];

    // Pour convertir le champ JSON en tableau
    protected $casts = [
        'commission_esthetique' => 'array',
    ];

    // Relation avec l'utilisateur qui a créé le projet
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
