<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $missing = DB::table('item_types')
            ->leftJoin('stocks', 'stocks.item_type_id', '=', 'item_types.id')
            ->whereNull('stocks.id')
            ->pluck('item_types.id');

        $now = now();

        foreach ($missing as $itemTypeId) {
            DB::table('stocks')->insertOrIgnore([
                'item_type_id' => $itemTypeId,
                'quantity_kg' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Sengaja tidak menghapus stok: baris ini juga dipakai transaksi lain.
    }
};
