<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $quantity = 'COALESCE(stocks.quantity_kg, 0)';

        $items = ItemType::query()
            ->leftJoin('stocks', 'stocks.item_type_id', '=', 'item_types.id')
            ->select('item_types.*', DB::raw($quantity.' as quantity_kg'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('item_types.name', 'ilike', '%'.$request->input('search').'%');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('item_types.category', $request->input('category'));
            })
            ->when($request->boolean('low_only'), function ($query) use ($quantity) {
                $query->whereRaw($quantity.' <= ?', [Stock::LOW_STOCK_KG]);
            })
            ->orderByRaw("CASE item_types.category
                WHEN 'grain' THEN 1
                WHEN 'rice' THEN 2
                WHEN 'bran' THEN 3
                WHEN 'husk' THEN 4
                ELSE 5 END")
            ->orderBy('item_types.name')
            ->paginate(10)
            ->withQueryString();

        $totals = ItemType::query()
            ->leftJoin('stocks', 'stocks.item_type_id', '=', 'item_types.id')
            ->selectRaw('item_types.category, SUM('.$quantity.') as total')
            ->groupBy('item_types.category')
            ->pluck('total', 'category');

        return view('stocks.index', compact('items', 'totals'));
    }

    public function edit(ItemType $item)
    {
        $item->quantity_kg = $item->stock->quantity_kg ?? 0; // sesuaikan dengan relasi Anda

        $movements = $item->stockMovements()
            ->with('user')
            ->latest()
            ->latest('id')
            ->limit(20)
            ->get();

        return view('stocks.edit', compact('item', 'movements'));
    }

    public function update(Request $request, ItemType $item)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out,set'],
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($item, $data) {
            Stock::query()->insertOrIgnore([
                'item_type_id' => $item->id,
                'quantity_kg' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $stock = Stock::where('item_type_id', $item->id)->lockForUpdate()->firstOrFail();
            $current = (float) $stock->quantity_kg;
            $amount = (float) $data['amount'];

            $new = match ($data['type']) {
                'in' => $current + $amount,
                'out' => $current - $amount,
                'set' => $amount,
            };

            if ($new < 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Stok tidak boleh menjadi negatif.',
                ]);
            }

            $stock->update(['quantity_kg' => $new]);

            StockMovement::create([
                'item_type_id' => $item->id,
                'user_id' => auth()->id(),
                'type' => $data['type'],
                'amount' => $amount,
                'quantity_before' => $current,
                'quantity_after' => $new,
                'note' => $data['note'] ?? null,
            ]);
        });

        return redirect()->route('stocks.index')->with('success', 'Stok berhasil diperbarui.');
    }
}
