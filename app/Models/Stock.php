<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}
