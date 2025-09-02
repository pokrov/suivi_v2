<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Examen extends Model
{
    protected $fillable = [
    'grand_projet_id',
    'numero_examen',
    'type_examen',
    'date_examen',
    'avis',
    'observations',
    'motifs',        // <—
    'motif_autre',   // <—
    'created_by',
];

protected $casts = [
    'date_examen' => 'date',
    'motifs'      => 'array', // <— important
];


    public function grandProjet() { return $this->belongsTo(GrandProjet::class); }
    public function auteur()      { return $this->belongsTo(User::class, 'created_by'); }
}
