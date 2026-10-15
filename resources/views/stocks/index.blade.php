@extends('layouts.app')

@section('title', 'Stok Gudang')

@section('content')
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="font-display text-3xl font-bold text-paddy-dark">Stok Gudang</h1>
        <p class="mt-1 text-sm text-muted">
            {{ $items->total() }} jenis barang · stok menipis jika ≤ {{ \App\Models\Stock::LOW_STOCK_KG }} kg
        </p>
    </div>

    {{-- Total per kategori --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        @foreach (\App\Models\ItemType::CATEGORIES as $key => $label)
            <div class="bg-card border border-line rounded-lg p-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-muted">Total {{ $label }}</p>
                <p data-testid="total-{{ $key }}" class="mt-2 font-display text-2xl font-bold text-paddy-dark">
                    {{ number_format((float) ($totals[$key] ?? 0), 2, ',', '.') }} kg
                </p>
            </div>
        @endforeach
    </div>

    {{-- Pencarian & filter --}}
    <form method="GET" action="{{ route('stocks.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 bg-card border border-line rounded-lg p-4">
        <div>
            <label for="search" class="block text-xs font-medium text-muted mb-1">Cari nama</label>
            <input id="search" name="search" type="text" value="{{ request('search') }}"
                placeholder="Ketik nama barang..."
                class="w-64 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
        </div>
        <div>
            <label for="filter_category" class="block text-xs font-medium text-muted mb-1">Saring kategori</label>
            <select id="filter_category" name="category"
                class="w-48 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                <option value="">Semua Kategori</option>
                @foreach (\App\Models\ItemType::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pb-2">
            <input id="filter_low" name="low_only" type="checkbox" value="1" @checked(request()->boolean('low_only'))
                class="h-4 w-4 rounded border-line accent-paddy">
            <label for="filter_low" class="text-sm">Hanya stok menipis</label>
        </div>
        <button type="submit"
            class="px-4 py-2 rounded-md bg-paddy-dark text-white text-sm font-medium hover:bg-paddy-deep transition">
            Terapkan
        </button>
        <a href="{{ route('stocks.index') }}"
            class="px-4 py-2 rounded-md border border-line text-sm text-ink hover:bg-paper transition">
            Reset
        </a>
    </form>

    {{-- Tabel --}}
    <div class="bg-card border border-line rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-paddy-soft text-paddy-dark">
                <tr class="text-left">
                    <th scope="col" class="px-4 py-3 w-14">No</th>
                    <th scope="col" class="px-4 py-3">Nama Barang</th>
                    <th scope="col" class="px-4 py-3">Kategori</th>
                    <th scope="col" class="px-4 py-3 text-right">Stok</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php $qty = (float) $item->quantity_kg; @endphp
                    <tr class="border-t border-line hover:bg-paper/60">
                        <td class="px-4 py-3 text-muted">{{ $items->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-paddy-soft text-paddy-dark text-xs font-medium">
                                {{ \App\Models\ItemType::CATEGORIES[$item->category] ?? $item->category }}
                            </span>
                        </td>
                        <td data-testid="stock-qty" class="px-4 py-3 text-right font-semibold">
                            {{ number_format($qty, 2, ',', '.') }} {{ $item->unit }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($qty <= 0)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-clay/10 text-clay text-xs font-medium">Habis</span>
                            @elseif ($qty <= \App\Models\Stock::LOW_STOCK_KG)
                                <span class="inline-block px-2 py-0.5 rounded-full bg-wheat/20 text-clay text-xs font-medium">Menipis</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-paddy-soft text-paddy-dark text-xs font-medium">Aman</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-muted">Tidak ada data stok yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection