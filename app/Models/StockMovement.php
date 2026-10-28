<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const TYPES = [
        'in' => 'Masuk',
        'out' => 'Keluar',
        'set' => 'Koreksi',
    ];

    protected $fillable = [
        'item_type_id',
        'user_id',
        'type',
        'amount',
        'quantity_before',
        'quantity_after',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'quantity_before' => 'decimal:2',
            'quantity_after' => 'decimal:2',
        ];
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
