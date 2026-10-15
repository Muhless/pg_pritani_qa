<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }
    public const CATEGORIES = [
        'grain' => 'Gabah',
        'rice' => 'Beras',
        'bran' => 'Bekatul',
        'husk' => 'Sekam',
        'broken_rice' => 'Menir',
    ];

    protected $fillable = ['name', 'category', 'unit'];
}
