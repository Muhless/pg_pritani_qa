<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    public const TYPES = [
        'store' => 'Toko',
        'distributor' => 'Distributor',
        'individual' => 'Perorangan',
    ];

    protected $fillable = ['name', 'address', 'phone', 'type'];
}
