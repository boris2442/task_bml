<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approbation extends Model
{
    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
