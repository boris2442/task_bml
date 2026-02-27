<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkAlert extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'heures_attendues',
        'heures_actuelles',
        'pourcentage',
        'periode_debut',
        'periode_fin',
        'notifiee',
        'notifiee_at',
    ];

    protected $casts = [
        'heures_attendues' => 'float',
        'heures_actuelles' => 'float',
        'pourcentage' => 'float',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'notifiee' => 'boolean',
        'notifiee_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
