<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
public function presence()
{
    return $this->belongsTo(Presence::class);
}
}
