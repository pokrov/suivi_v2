<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Examen extends Model
{
    protected $fillable = [
        'grand_projet_id',
        'numero_examen',
        'type_examen',     // ajouté
        'date_examen',     // renommé
        'avis',
        'observations',
        'created_by',
    ];

    public function grandProjet() { return $this->belongsTo(GrandProjet::class); }
    public function auteur()      { return $this->belongsTo(User::class, 'created_by'); }
}
