<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function millingProcess()
    {
        return $this->hasOne(MillingProcess::class);
    }
}
