<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'heure_arrivee',
        'heure_depart',
        'date_presence',
        'statut',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function justificatifs()
    {
        return $this->hasMany(Justificatif::class);
    }

    public function approbations()
    {
        return $this->hasMany(Approbation::class);
    }

    // Attribut calculé pour les heures
    public function getHeuresTravailleesAttribute()
    {
        if ($this->heure_depart) {
            return $this->heure_depart->diffInHours($this->heure_arrivee);
        }
        return 0;
    }
}
