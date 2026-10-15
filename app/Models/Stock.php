<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    public const LOW_STOCK_KG = 100;
    protected $fillable = ['item_type_id', 'quantity_kg'];
    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}
