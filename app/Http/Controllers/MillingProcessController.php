<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use App\Models\MillingProcess;
use App\Models\Purchase;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MillingProcessController extends Controller
{
    public function index(Request $request)
    {
        $processes = MillingProcess::query()
            ->with(['purchase.farmer', 'purchase.itemType'])
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('processed_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('processed_at', '<=', $request->input('date_to'));
            })
            ->orderByDesc('processed_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $availablePurchases = Purchase::query()
            ->doesntHave('millingProcess')
            ->with(['farmer', 'itemType'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $purchaseWeights = $availablePurchases->mapWithKeys(
            fn($purchase) => [$purchase->id => ['net' => (float) $purchase->net_weight_kg]]
        );

        $riceTypes = ItemType::where('category', 'rice')->orderBy('name')->get(['id', 'name']);

        return view('milling.index', compact('processes', 'availablePurchases', 'purchaseWeights', 'riceTypes'));
    }

    public function store(Request $request)
    {
        $purchaseId = (string) $request->input('purchase_id');
        $purchase = ctype_digit($purchaseId) ? Purchase::find($purchaseId) : null;

        $validator = Validator::make($request->all(), $this->rules($purchase), $this->messages());

        $validator->after(function ($validator) use ($request, $purchase) {
            if ($validator->errors()->isNotEmpty() || !$purchase) {
                return;
            }

            $total = round(
                (float) $request->input('rice_output_kg')
                + (float) $request->input('bran_output_kg')
                + (float) $request->input('husk_output_kg')
                + (float) $request->input('broken_rice_output_kg'),
                2
            );
            $input = round((float) $purchase->net_weight_kg, 2);

            if ($total > $input) {
                $validator->errors()->add(
                    'total_output',
                    'Total hasil giling (' . number_format($total, 2, ',', '.') . ' kg) melebihi berat gabah masuk ('
                    . number_format($input, 2, ',', '.') . ' kg).'
                );
            }
        });

        $validated = $validator->validate();

        $input = (float) $purchase->net_weight_kg;
        $rice = (float) $validated['rice_output_kg'];
        $bran = (float) ($validated['bran_output_kg'] ?? 0);
        $husk = (float) ($validated['husk_output_kg'] ?? 0);
        $broken = (float) ($validated['broken_rice_output_kg'] ?? 0);
        $yield = round($rice / $input * 100, 2);

        $process = DB::transaction(function () use ($validated, $purchase, $input, $rice, $bran, $husk, $broken, $yield) {
            $grainStock = Stock::where('item_type_id', $purchase->item_type_id)
                ->lockForUpdate()
                ->first();

            if (!$grainStock || (float) $grainStock->quantity_kg < $input) {
                return null;
            }

            $branTypeId = $bran > 0 ? $this->byProduct('bran', 'Bekatul')->id : null;
            $huskTypeId = $husk > 0 ? $this->byProduct('husk', 'Sekam')->id : null;
            $brokenTypeId = $broken > 0 ? $this->byProduct('broken_rice', 'Menir')->id : null;

            $process = MillingProcess::create([
                'purchase_id' => $purchase->id,
                'input_weight_kg' => $input,
                'rice_item_type_id' => $validated['rice_item_type_id'],
                'rice_output_kg' => $rice,
                'bran_item_type_id' => $branTypeId,
                'bran_output_kg' => $bran,
                'husk_item_type_id' => $huskTypeId,
                'husk_output_kg' => $husk,
                'broken_rice_item_type_id' => $brokenTypeId,
                'broken_rice_output_kg' => $broken,
                'yield_percentage' => $yield,
                'processed_at' => Carbon::parse($validated['processed_at']),
                'created_by' => auth()->id(),
            ]);

            $grainStock->decrement('quantity_kg', $input);
            $this->addStock($validated['rice_item_type_id'], $rice);

            if ($branTypeId) {
                $this->addStock($branTypeId, $bran);
            }
            if ($huskTypeId) {
                $this->addStock($huskTypeId, $husk);
            }
            if ($brokenTypeId) {
                $this->addStock($brokenTypeId, $broken);
            }

            return $process;
        });

        if (!$process) {
            return redirect()->route('milling.index')
                ->with('error', 'Stok gabah tidak mencukupi untuk diproses giling.');
        }

        $redirect = redirect()->route('milling.index')
            ->with('success', 'Proses giling berhasil dicatat dan stok diperbarui.');

        if (!$process->isYieldNormal()) {
            $redirect->with('warning', 'Rendemen ' . number_format($yield, 2, ',', '.') . '% berada di luar rentang normal ('
                . MillingProcess::MIN_NORMAL_YIELD . '–' . MillingProcess::MAX_NORMAL_YIELD . '%). Periksa kembali data hasil giling.');
        }

        return $redirect;
    }

    public function destroy(MillingProcess $millingProcess)
    {
        $error = DB::transaction(function () use ($millingProcess) {
            $stocks = [];

            foreach ($millingProcess->outputs() as $output) {
                $stock = Stock::where('item_type_id', $output['item_type_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stock || (float) $stock->quantity_kg < $output['quantity']) {
                    return 'Proses giling tidak dapat dihapus karena stok hasil giling sudah berkurang.';
                }

                $stocks[] = [$stock, $output['quantity']];
            }

            foreach ($stocks as [$stock, $quantity]) {
                $stock->decrement('quantity_kg', $quantity);
            }

            $this->addStock($millingProcess->purchase->item_type_id, (float) $millingProcess->input_weight_kg);
            $millingProcess->delete();

            return null;
        });

        if ($error) {
            return redirect()->route('milling.index')->with('error', $error);
        }

        return redirect()->route('milling.index')
            ->with('success', 'Proses giling dihapus. Stok hasil giling dikurangi dan stok gabah dikembalikan.');
    }

    private function rules(?Purchase $purchase): array
    {
        return [
            'purchase_id' => [
                'bail',
                'required',
                'integer',
                'exists:purchases,id',
                Rule::unique('milling_processes', 'purchase_id'),
            ],
            'rice_item_type_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('item_types', 'id')->where('category', 'rice'),
            ],
            'processed_at' => [
                'bail',
                'required',
                'date',
                'before_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail) use ($purchase) {
                    if ($purchase && Carbon::parse($value)->toDateString() < $purchase->purchase_date->toDateString()) {
                        $fail('Tanggal giling tidak boleh sebelum tanggal pembelian ('
                            . $purchase->purchase_date->format('d/m/Y') . ').');
                    }
                },
            ],
            'rice_output_kg' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'bran_output_kg' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'husk_output_kg' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'broken_rice_output_kg' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
        ];
    }

    private function messages(): array
    {
        return [
            'purchase_id.required' => 'Pembelian gabah wajib dipilih.',
            'purchase_id.integer' => 'Pembelian gabah tidak valid.',
            'purchase_id.exists' => 'Pembelian gabah tidak valid.',
            'purchase_id.unique' => 'Pembelian ini sudah diproses giling.',
            'rice_item_type_id.required' => 'Jenis beras hasil wajib dipilih.',
            'rice_item_type_id.integer' => 'Jenis beras tidak valid.',
            'rice_item_type_id.exists' => 'Jenis beras tidak valid. Hanya barang berkategori beras yang dapat dipilih.',
            'processed_at.required' => 'Tanggal giling wajib diisi.',
            'processed_at.date' => 'Format tanggal tidak valid.',
            'processed_at.before_or_equal' => 'Tanggal giling tidak boleh melebihi hari ini.',
            'rice_output_kg.required' => 'Hasil beras wajib diisi.',
            'rice_output_kg.numeric' => 'Hasil beras harus berupa angka.',
            'rice_output_kg.decimal' => 'Hasil beras maksimal 2 angka di belakang koma.',
            'rice_output_kg.gt' => 'Hasil beras harus lebih dari 0.',
            'bran_output_kg.numeric' => 'Hasil bekatul harus berupa angka.',
            'bran_output_kg.decimal' => 'Hasil bekatul maksimal 2 angka di belakang koma.',
            'bran_output_kg.min' => 'Hasil bekatul tidak boleh negatif.',
            'husk_output_kg.numeric' => 'Hasil sekam harus berupa angka.',
            'husk_output_kg.decimal' => 'Hasil sekam maksimal 2 angka di belakang koma.',
            'husk_output_kg.min' => 'Hasil sekam tidak boleh negatif.',
            'broken_rice_output_kg.numeric' => 'Hasil menir harus berupa angka.',
            'broken_rice_output_kg.decimal' => 'Hasil menir maksimal 2 angka di belakang koma.',
            'broken_rice_output_kg.min' => 'Hasil menir tidak boleh negatif.',
        ];
    }

    private function byProduct(string $category, string $name): ItemType
    {
        return ItemType::firstOrCreate(
            ['name' => $name, 'category' => $category],
            ['unit' => 'kg']
        );
    }

    private function addStock(int|string $itemTypeId, float $quantity): void
    {
        $stock = Stock::firstOrCreate(
            ['item_type_id' => $itemTypeId],
            ['quantity_kg' => 0]
        );

        $stock->increment('quantity_kg', $quantity);
    }
}