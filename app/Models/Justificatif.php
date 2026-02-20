<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justificatif extends Model
{
    protected $guarded = [];
public function presence()
{
    return $this->belongsTo(Presence::class);
}
}
