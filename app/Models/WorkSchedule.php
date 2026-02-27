<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'heures_par_jour',
        'jours_travailles',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'heures_par_jour' => 'float',
        'jours_travailles' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
