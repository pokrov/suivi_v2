<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluxEtape extends Model
{
    protected $fillable = [
        'grand_projet_id', 'from_etat', 'to_etat', 'happened_at', 'by_user', 'note',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
    ];

    public function grandProjet()
    {
        return $this->belongsTo(GrandProjet::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'by_user');
    }
}
