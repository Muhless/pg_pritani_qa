<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MillingProcess extends Model
{
    public const MIN_NORMAL_YIELD = 55;
    public const MAX_NORMAL_YIELD = 70;

    protected $fillable = [
        'purchase_id',
        'input_weight_kg',
        'rice_item_type_id',
        'rice_output_kg',
        'bran_item_type_id',
        'bran_output_kg',
        'husk_item_type_id',
        'husk_output_kg',
        'broken_rice_item_type_id',
        'broken_rice_output_kg',
        'yield_percentage',
        'processed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function riceItemType()
    {
        return $this->belongsTo(ItemType::class, 'rice_item_type_id');
    }

    public function branItemType()
    {
        return $this->belongsTo(ItemType::class, 'bran_item_type_id');
    }

    public function huskItemType()
    {
        return $this->belongsTo(ItemType::class, 'husk_item_type_id');
    }

    public function brokenRiceItemType()
    {
        return $this->belongsTo(ItemType::class, 'broken_rice_item_type_id');
    }

    public function isYieldNormal(): bool
    {
        $yield = (float) $this->yield_percentage;

        return $yield >= self::MIN_NORMAL_YIELD && $yield <= self::MAX_NORMAL_YIELD;
    }

    /**
     * Daftar hasil giling yang masuk stok: [['item_type_id' => .., 'quantity' => ..], ...]
     */
    public function outputs(): array
    {
        $pairs = [
            [$this->rice_item_type_id, $this->rice_output_kg],
            [$this->bran_item_type_id, $this->bran_output_kg],
            [$this->husk_item_type_id, $this->husk_output_kg],
            [$this->broken_rice_item_type_id, $this->broken_rice_output_kg],
        ];

        return collect($pairs)
            ->filter(fn($pair) => $pair[0] && (float) $pair[1] > 0)
            ->map(fn($pair) => ['item_type_id' => $pair[0], 'quantity' => (float) $pair[1]])
            ->values()
            ->all();
    }
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
