<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer',
        'credit' => 'Tempo',
    ];

    public const SELLABLE_CATEGORIES = ['rice', 'bran', 'husk', 'broken_rice'];

    public const STATUS_PAID = 'paid';
    public const STATUS_UNPAID = 'unpaid';

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'payment_method',
        'total_amount',
        'sale_date',
        'due_date',
        'payment_status',
        'paid_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === self::STATUS_UNPAID;
    }

    public function isOverdue(): bool
    {
        return $this->isUnpaid() && $this->due_date && $this->due_date->lt(today());
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
