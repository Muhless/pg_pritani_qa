<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $quantity = 'COALESCE(stocks.quantity_kg, 0)';

        $items = ItemType::query()
            ->leftJoin('stocks', 'stocks.item_type_id', '=', 'item_types.id')
            ->select('item_types.*', DB::raw($quantity . ' as quantity_kg'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('item_types.name', 'ilike', '%' . $request->input('search') . '%');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('item_types.category', $request->input('category'));
            })
            ->when($request->boolean('low_only'), function ($query) use ($quantity) {
                $query->whereRaw($quantity . ' <= ?', [Stock::LOW_STOCK_KG]);
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
            ->selectRaw('item_types.category, SUM(' . $quantity . ') as total')
            ->groupBy('item_types.category')
            ->pluck('total', 'category');

        return view('stocks.index', compact('items', 'totals'));
    }
}