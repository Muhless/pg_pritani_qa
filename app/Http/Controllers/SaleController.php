<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ItemType;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    private const MAX_TOTAL = 9999999999.99;

    public function index(Request $request)
    {
        $sales = Sale::query()
            ->with(['customer', 'items.itemType'])
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->input('customer_id'));
            })
            ->when($request->filled('payment_method'), function ($query) use ($request) {
                $query->where('payment_method', $request->input('payment_method'));
            })
            ->when($request->filled('payment_status'), function ($query) use ($request) {
                $query->where('payment_status', $request->input('payment_status'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('sale_date', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('sale_date', '<=', $request->input('date_to'));
            })
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        $sellable = ItemType::whereIn('category', Sale::SELLABLE_CATEGORIES)
            ->orderBy('name')
            ->get(['id', 'name', 'category']);

        $itemGroups = $sellable->groupBy('category');

        $stockMap = Stock::whereIn('item_type_id', $sellable->pluck('id'))
            ->pluck('quantity_kg', 'item_type_id')
            ->map(fn($quantity) => (float) $quantity)
            ->all();

        return view('sales.index', compact('sales', 'customers', 'itemGroups', 'stockMap'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $total = collect($request->input('items', []))->sum(function ($item) {
                return round((float) ($item['quantity_kg'] ?? 0) * (float) ($item['price_per_kg'] ?? 0), 2);
            });

            if ($total > self::MAX_TOTAL) {
                $validator->errors()->add('items', 'Total transaksi melebihi batas maksimal Rp 9.999.999.999.');
            }
        });

        $validated = $validator->validate();
        $isCredit = $validated['payment_method'] === 'credit';

        $lines = collect($validated['items'])->map(function ($item, $index) {
            $quantity = round((float) $item['quantity_kg'], 2);
            $price = round((float) $item['price_per_kg'], 2);

            return [
                'index' => $index,
                'item_type_id' => (int) $item['item_type_id'],
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => round($quantity * $price, 2),
            ];
        })->values();

        $total = round($lines->sum('subtotal'), 2);

        $sale = DB::transaction(function () use ($validated, $isCredit, $lines, $total) {
            $stocks = [];
            $errors = [];

            // Kunci stok berurutan berdasarkan id barang supaya tidak terjadi deadlock
            foreach ($lines->sortBy('item_type_id') as $line) {
                $stock = Stock::where('item_type_id', $line['item_type_id'])
                    ->lockForUpdate()
                    ->first();

                $available = $stock ? round((float) $stock->quantity_kg, 2) : 0.0;

                if ($available < $line['quantity']) {
                    $name = ItemType::find($line['item_type_id'])?->name;
                    $errors['items.' . $line['index'] . '.quantity_kg'] = 'Stok ' . $name
                        . ' tidak mencukupi. Tersedia: ' . number_format($available, 2, ',', '.') . ' kg.';
                    continue;
                }

                $stocks[$line['item_type_id']] = $stock;
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'invoice_number' => null,
                'payment_method' => $validated['payment_method'],
                'total_amount' => $total,
                'sale_date' => $validated['sale_date'],
                'due_date' => $isCredit ? $validated['due_date'] : null,
                'payment_status' => $isCredit ? Sale::STATUS_UNPAID : Sale::STATUS_PAID,
                'paid_at' => $isCredit ? null : now(),
                'created_by' => auth()->id(),
            ]);

            $sale->update([
                'invoice_number' => 'INV-' . $sale->sale_date->format('Ymd') . '-' . str_pad($sale->id, 4, '0', STR_PAD_LEFT),
            ]);

            foreach ($lines as $line) {
                $sale->items()->create([
                    'item_type_id' => $line['item_type_id'],
                    'quantity_kg' => $line['quantity'],
                    'price_per_kg' => $line['price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $stocks[$line['item_type_id']]->decrement('quantity_kg', $line['quantity']);
            }

            return $sale;
        });

        return redirect()->route('sales.index')
            ->with('success', 'Penjualan ' . $sale->invoice_number . ' berhasil dicatat.');
    }

    public function markPaid(Sale $sale)
    {
        $updated = Sale::where('id', $sale->id)
            ->where('payment_status', Sale::STATUS_UNPAID)
            ->update([
                'payment_status' => Sale::STATUS_PAID,
                'paid_at' => now(),
            ]);

        if (!$updated) {
            return redirect()->route('sales.index')
                ->with('error', 'Penjualan ini sudah lunas.');
        }

        return redirect()->route('sales.index')
            ->with('success', 'Piutang ' . $sale->invoice_number . ' ditandai lunas.');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $stock = Stock::firstOrCreate(
                    ['item_type_id' => $item->item_type_id],
                    ['quantity_kg' => 0]
                );

                $stock->increment('quantity_kg', (float) $item->quantity_kg);
            }

            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Penjualan ' . $sale->invoice_number . ' dihapus dan stok dikembalikan.');
    }

    private function rules(): array
    {
        return [
            'customer_id' => ['bail', 'required', 'integer', 'exists:customers,id'],
            'sale_date' => ['bail', 'required', 'date', 'before_or_equal:today'],
            'payment_method' => ['bail', 'required', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'due_date' => ['exclude_unless:payment_method,credit', 'bail', 'required', 'date', 'after_or_equal:sale_date'],
            'items' => ['bail', 'required', 'array', 'min:1', 'max:5'],
            'items.*.item_type_id' => [
                'bail',
                'required',
                'integer',
                'distinct',
                Rule::exists('item_types', 'id')->whereIn('category', Sale::SELLABLE_CATEGORIES),
            ],
            'items.*.quantity_kg' => ['bail', 'required', 'numeric', 'decimal:0,2', 'gt:0', 'max:50000'],
            'items.*.price_per_kg' => ['bail', 'required', 'numeric', 'decimal:0,2', 'gt:0', 'max:100000'],
        ];
    }

    private function messages(): array
    {
        return [
            'customer_id.required' => 'Pelanggan wajib dipilih.',
            'customer_id.integer' => 'Pelanggan tidak valid.',
            'customer_id.exists' => 'Pelanggan tidak valid.',
            'sale_date.required' => 'Tanggal penjualan wajib diisi.',
            'sale_date.date' => 'Format tanggal tidak valid.',
            'sale_date.before_or_equal' => 'Tanggal penjualan tidak boleh melebihi hari ini.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'due_date.required' => 'Tanggal jatuh tempo wajib diisi untuk penjualan tempo.',
            'due_date.date' => 'Format tanggal jatuh tempo tidak valid.',
            'due_date.after_or_equal' => 'Jatuh tempo tidak boleh sebelum tanggal penjualan.',
            'items.required' => 'Minimal satu barang harus diisi.',
            'items.array' => 'Daftar barang tidak valid.',
            'items.min' => 'Minimal satu barang harus diisi.',
            'items.max' => 'Maksimal 5 barang per transaksi.',
            'items.*.item_type_id.required' => 'Barang wajib dipilih.',
            'items.*.item_type_id.integer' => 'Barang tidak valid.',
            'items.*.item_type_id.distinct' => 'Barang tidak boleh sama dalam satu transaksi.',
            'items.*.item_type_id.exists' => 'Barang tidak valid. Hanya beras, bekatul, sekam, dan menir yang dapat dijual.',
            'items.*.quantity_kg.required' => 'Berat wajib diisi.',
            'items.*.quantity_kg.numeric' => 'Berat harus berupa angka.',
            'items.*.quantity_kg.decimal' => 'Berat maksimal 2 angka di belakang koma.',
            'items.*.quantity_kg.gt' => 'Berat harus lebih dari 0.',
            'items.*.quantity_kg.max' => 'Berat maksimal 50.000 kg per barang.',
            'items.*.price_per_kg.required' => 'Harga per kg wajib diisi.',
            'items.*.price_per_kg.numeric' => 'Harga per kg harus berupa angka.',
            'items.*.price_per_kg.decimal' => 'Harga per kg maksimal 2 angka di belakang koma.',
            'items.*.price_per_kg.gt' => 'Harga per kg harus lebih dari 0.',
            'items.*.price_per_kg.max' => 'Harga per kg maksimal Rp 100.000.',
        ];
    }
}