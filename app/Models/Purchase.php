<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'farmer_id',
        'item_type_id',
        'weight_kg',
        'net_weight_kg',
        'price_per_kg',
        'moisture_deduction',
        'total_price',
        'purchase_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }
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
