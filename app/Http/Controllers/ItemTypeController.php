<?php

namespace App\Http\Controllers;

use App\Models\ItemType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemTypeController extends Controller
{
    public function index(Request $request)
    {
        $itemTypes = ItemType::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'ilike', '%' . $request->input('search') . '%');
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->input('category'));
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('item-types.index', compact('itemTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request), $this->messages());

        ItemType::create($validated);

        return redirect()->route('item-types.index')
            ->with('success', 'Jenis barang berhasil ditambahkan.');
    }

    public function update(Request $request, ItemType $itemType)
    {
        $validated = $request->validate($this->rules($request, $itemType), $this->messages());

        $itemType->update($validated);

        return redirect()->route('item-types.index')
            ->with('success', 'Jenis barang berhasil diperbarui.');
    }

    public function destroy(ItemType $itemType)
    {
        try {
            $itemType->delete();
        } catch (QueryException $e) {
            // 23503 = foreign key violation (masih dipakai pembelian, penjualan, atau stok)
            if ($e->getCode() === '23503') {
                return redirect()->route('item-types.index')
                    ->with('error', 'Jenis barang tidak dapat dihapus karena masih dipakai pada transaksi atau stok.');
            }

            throw $e;
        }

        return redirect()->route('item-types.index')
            ->with('success', 'Jenis barang berhasil dihapus.');
    }

    private function rules(Request $request, ?ItemType $itemType = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('item_types', 'name')
                    ->where('category', $request->input('category'))
                    ->ignore($itemType?->id),
            ],
            'category' => ['required', Rule::in(array_keys(ItemType::CATEGORIES))],
            'unit' => ['required', 'string', 'max:20'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama barang wajib diisi.',
            'name.max' => 'Nama barang maksimal 100 karakter.',
            'name.unique' => 'Nama barang sudah ada pada kategori ini.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'unit.required' => 'Satuan wajib diisi.',
            'unit.max' => 'Satuan maksimal 20 karakter.',
        ];
    }
}