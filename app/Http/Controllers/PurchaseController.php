<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\ItemType;
use App\Models\Purchase;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::query()
            ->with(['farmer', 'itemType'])
            ->when($request->filled('farmer_id'), function ($query) use ($request) {
                $query->where('farmer_id', $request->input('farmer_id'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('purchase_date', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('purchase_date', '<=', $request->input('date_to'));
            })
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $farmers = Farmer::orderBy('name')->get(['id', 'name']);
        $grains = ItemType::where('category', 'grain')->orderBy('name')->get(['id', 'name']);

        return view('purchases.index', compact('purchases', 'farmers', 'grains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $weight = (float) $validated['weight_kg'];
        $pricePerKg = (float) $validated['price_per_kg'];
        $moisture = (float) ($validated['moisture_deduction'] ?? 0);

        $netWeight = round($weight * (1 - $moisture / 100), 2);
        $total = round($netWeight * $pricePerKg, 2);

        DB::transaction(function () use ($validated, $moisture, $netWeight, $total) {
            Purchase::create(array_merge($validated, [
                'moisture_deduction' => $moisture,
                'net_weight_kg' => $netWeight,
                'total_price' => $total,
                'created_by' => auth()->id(),
            ]));

            $stock = Stock::firstOrCreate(
                ['item_type_id' => $validated['item_type_id']],
                ['quantity_kg' => 0]
            );
            $stock->increment('quantity_kg', $netWeight);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian gabah berhasil dicatat.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->millingProcess()->exists()) {
            return redirect()->route('purchases.index')
                ->with('error', 'Pembelian tidak dapat dihapus karena sudah diproses giling.');
        }

        DB::transaction(function () use ($purchase) {
            $stock = Stock::where('item_type_id', $purchase->item_type_id)
                ->lockForUpdate()
                ->first();

            $stock?->decrement('quantity_kg', $purchase->net_weight_kg);
            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Pembelian gabah berhasil dihapus dan stok disesuaikan.');
    }

    private function rules(): array
    {
        return [
            'farmer_id' => ['required', 'exists:farmers,id'],
            'item_type_id' => [
                'required',
                Rule::exists('item_types', 'id')->where('category', 'grain'),
            ],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'weight_kg' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:50000'],
            'price_per_kg' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:100000'],
            'moisture_deduction' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99.99'],
        ];
    }

    private function messages(): array
    {
        return [
            'farmer_id.required' => 'Petani wajib dipilih.',
            'farmer_id.exists' => 'Petani tidak valid.',
            'item_type_id.required' => 'Jenis gabah wajib dipilih.',
            'item_type_id.exists' => 'Jenis gabah tidak valid. Hanya barang berkategori gabah yang dapat dibeli.',
            'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
            'purchase_date.date' => 'Format tanggal tidak valid.',
            'purchase_date.before_or_equal' => 'Tanggal pembelian tidak boleh melebihi hari ini.',
            'weight_kg.required' => 'Berat gabah wajib diisi.',
            'weight_kg.numeric' => 'Berat gabah harus berupa angka.',
            'weight_kg.decimal' => 'Berat gabah maksimal 2 angka di belakang koma.',
            'weight_kg.gt' => 'Berat gabah harus lebih dari 0.',
            'weight_kg.max' => 'Berat gabah maksimal 50.000 kg.',
            'price_per_kg.required' => 'Harga per kg wajib diisi.',
            'price_per_kg.numeric' => 'Harga per kg harus berupa angka.',
            'price_per_kg.decimal' => 'Harga per kg maksimal 2 angka di belakang koma.',
            'price_per_kg.gt' => 'Harga per kg harus lebih dari 0.',
            'price_per_kg.max' => 'Harga per kg maksimal Rp 100.000.',
            'moisture_deduction.numeric' => 'Potongan kadar air harus berupa angka.',
            'moisture_deduction.decimal' => 'Potongan kadar air maksimal 2 angka di belakang koma.',
            'moisture_deduction.min' => 'Potongan kadar air tidak boleh negatif.',
            'moisture_deduction.max' => 'Potongan kadar air maksimal 99,99%.',
        ];
    }
}
