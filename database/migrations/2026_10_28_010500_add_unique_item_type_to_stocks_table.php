<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index unik lama tidak pernah terbentuk karena unique() dirantai setelah
     * constrained() (yang mengembalikan ForeignKeyDefinition, bukan ColumnDefinition).
     * Akibatnya insertOrIgnore() di StockController bisa membuat baris stok ganda.
     */
    public function up(): void
    {
        $duplicates = DB::table('stocks')
            ->select('item_type_id', DB::raw('MIN(id) as keep_id'), DB::raw('SUM(quantity_kg) as total'))
            ->groupBy('item_type_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('stocks')->where('id', $duplicate->keep_id)->update([
                'quantity_kg' => $duplicate->total,
                'updated_at' => now(),
            ]);

            DB::table('stocks')
                ->where('item_type_id', $duplicate->item_type_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        if (! Schema::hasIndex('stocks', 'stocks_item_type_id_unique')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->unique('item_type_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('stocks', 'stocks_item_type_id_unique')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropUnique(['item_type_id']);
            });
        }
    }
};
