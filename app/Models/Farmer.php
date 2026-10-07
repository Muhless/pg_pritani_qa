<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
