<?php

namespace App\Http\Controllers;

use App\Models\MillingProcess;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $gabahMasukToday = Purchase::whereDate('purchase_date', $today)->sum('net_weight_kg');
        $gabahMasukTotal = Purchase::sum('net_weight_kg');

        $berasStok = 0;
        if (class_exists(\App\Models\Stock::class)) {
            $berasStok = \App\Models\Stock::whereHas('itemType', function ($q) {
                $q->where('category', 'rice');
            })->sum('quantity_kg');
        }

        $penjualanToday = Sale::whereDate('sale_date', $today)->count();
        $penjualanTotal = Sale::count();

        $millingProcesses = MillingProcess::with('purchase')->get();
        $totalInput = 0;
        $totalRice = 0;
        foreach ($millingProcesses as $mp) {
            $totalInput += (float) ($mp->purchase->net_weight_kg ?? 0);
            $totalRice += (float) $mp->rice_output_kg;
        }
        $avgRendemen = $totalInput > 0 ? round(($totalRice / $totalInput) * 100, 1) : 0;

        return view('dashboard', compact(
            'gabahMasukToday',
            'gabahMasukTotal',
            'berasStok',
            'penjualanToday',
            'penjualanTotal',
            'avgRendemen'
        ));
    }
}
