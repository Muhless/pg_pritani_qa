<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'item_type_id',
        'quantity_kg',
        'price_per_kg',
        'subtotal',
    ];
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}
