<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaitreOeuvre extends Model
{
    use HasFactory;

    protected $table = 'maitre_oeuvres'; // Nom de la table

    protected $fillable = ['nom']; // Champs autorisés en mass assignment
}
