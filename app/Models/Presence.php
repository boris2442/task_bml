<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\WorkHoursCalculationService;

class Presence extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'heure_arrivee',
        'heure_depart',
        'date_presence',
        'statut',
        'heures_travaillees',
        'heures_supplementaires',
    ];

    protected $casts = [
        'heure_arrivee' => 'datetime',
        'heure_depart' => 'datetime',
        'date_presence' => 'date',
        'heures_travaillees' => 'float',
        'heures_supplementaires' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        /**
         * Calculer automatiquement les heures travaillées et supplémentaires
         * quand une présence est sauvegardée
         */
        static::saving(function ($presence) {
            if ($presence->heure_depart && $presence->heure_arrivee) {
                $totalHeures = $presence->heure_depart->diffInHours($presence->heure_arrivee);

                $service = new WorkHoursCalculationService();
                $heures = $service->calculerHeuresTravailleesEtSupp($totalHeures);

                $presence->heures_travaillees = $heures['heures_travaillees'];
                $presence->heures_supplementaires = $heures['heures_supplementaires'];
            }
        });
    }

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

    /**
     * Obtenir les heures affichées (pour compatibilité avec ancien code)
     */
    public function getHeuresTravailleesAttributeCompat()
    {
        if ($this->heure_depart) {
            return $this->heure_depart->diffInHours($this->heure_arrivee);
        }
        return 0;
    }
}
