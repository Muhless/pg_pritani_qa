@extends('layouts.app')

@section('title', 'Pembelian Gabah')

@section('content')
<div
    x-data="{
        open: @js($errors->any()),
        form: {
            farmer_id: @js(old('farmer_id', '')),
            item_type_id: @js(old('item_type_id', '')),
            purchase_date: @js(old('purchase_date', now()->toDateString())),
            weight_kg: @js(old('weight_kg', '')),
            price_per_kg: @js(old('price_per_kg', '')),
            moisture_deduction: @js(old('moisture_deduction', '')),
        },
        get netWeight() {
            const weight = parseFloat(this.form.weight_kg) || 0;
            const moisture = parseFloat(this.form.moisture_deduction) || 0;
            return Math.round(weight * (1 - moisture / 100) * 100) / 100;
        },
        get total() {
            const price = parseFloat(this.form.price_per_kg) || 0;
            return Math.round(this.netWeight * price * 100) / 100;
        },
        kg(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value) + ' kg';
        },
        rupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        },
        openAdd() {
            this.form = {
                farmer_id: '',
                item_type_id: '',
                purchase_date: '{{ now()->toDateString() }}',
                weight_kg: '',
                price_per_kg: '',
                moisture_deduction: '',
            };
            this.open = true;
        },
        confirmDelete(formEl, code, farmer) {
            Swal.fire({
                title: 'Hapus pembelian?',
                text: code + ' dari ' + farmer + ' akan dihapus dan stok gabah dikurangi.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#b4532a',
                cancelButtonColor: '#6b7c68',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) formEl.submit();
            });
        },
    }"
    @keydown.escape.window="open = false"
>
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-paddy-dark">Pembelian Gabah</h1>
            <p class="mt-1 text-sm text-muted">{{ $purchases->total() }} transaksi pembelian</p>
        </div>
        <button type="button" @click="openAdd()"
            class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Tambah Pembelian
        </button>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div role="status" class="mb-4 px-4 py-3 rounded-md bg-paddy-soft text-paddy-dark text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div role="alert" class="mb-4 px-4 py-3 rounded-md bg-clay/10 text-clay text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('purchases.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 bg-card border border-line rounded-lg p-4">
        <div>
            <label for="filter_farmer" class="block text-xs font-medium text-muted mb-1">Saring petani</label>
            <select id="filter_farmer" name="farmer_id"
                class="w-52 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                <option value="">Semua Petani</option>
                @foreach ($farmers as $farmer)
                    <option value="{{ $farmer->id }}" @selected((string) request('farmer_id') === (string) $farmer->id)>
                        {{ $farmer->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter_date_from" class="block text-xs font-medium text-muted mb-1">Tanggal dari</label>
            <input id="filter_date_from" name="date_from" type="date" value="{{ request('date_from') }}"
                class="px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
        </div>
        <div>
            <label for="filter_date_to" class="block text-xs font-medium text-muted mb-1">Tanggal sampai</label>
            <input id="filter_date_to" name="date_to" type="date" value="{{ request('date_to') }}"
                class="px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
        </div>
        <button type="submit"
            class="px-4 py-2 rounded-md bg-paddy-dark text-white text-sm font-medium hover:bg-paddy-deep transition">
            Terapkan
        </button>
        <a href="{{ route('purchases.index') }}"
            class="px-4 py-2 rounded-md border border-line text-sm text-ink hover:bg-paper transition">
            Reset
        </a>
    </form>

    {{-- Tabel --}}
    <div class="bg-card border border-line rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-paddy-soft text-paddy-dark">
                <tr class="text-left">
                    <th scope="col" class="px-4 py-3">Kode</th>
                    <th scope="col" class="px-4 py-3">Tanggal</th>
                    <th scope="col" class="px-4 py-3">Petani</th>
                    <th scope="col" class="px-4 py-3">Jenis Gabah</th>
                    <th scope="col" class="px-4 py-3 text-right">Berat</th>
                    <th scope="col" class="px-4 py-3 text-right">Potongan</th>
                    <th scope="col" class="px-4 py-3 text-right">Berat Bersih</th>
                    <th scope="col" class="px-4 py-3 text-right">Harga/kg</th>
                    <th scope="col" class="px-4 py-3 text-right">Total</th>
                    <th scope="col" class="px-4 py-3 w-20 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    @php $code = 'PB-' . str_pad($purchase->id, 4, '0', STR_PAD_LEFT); @endphp
                    <tr class="border-t border-line hover:bg-paper/60">
                        <td class="px-4 py-3 font-mono text-xs">{{ $code }}</td>
                        <td class="px-4 py-3">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $purchase->farmer->name }}</td>
                        <td class="px-4 py-3">{{ $purchase->itemType->name }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($purchase->weight_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right">{{ number_format($purchase->moisture_deduction, 2, ',', '.') }}%</td>
                        <td class="px-4 py-3 text-right">{{ number_format($purchase->net_weight_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($purchase->price_per_kg, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end">
                                <form method="POST" action="{{ route('purchases.destroy', $purchase) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        aria-label="Hapus pembelian {{ $code }}"
                                        @click="confirmDelete($el.closest('form'), @js($code), @js($purchase->farmer->name))"
                                        class="px-2.5 py-1 rounded border border-clay/30 text-xs text-clay hover:bg-clay/10 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-muted">Belum ada data pembelian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>

    {{-- Modal tambah --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4"
        x-transition.opacity>
        <div role="dialog" aria-modal="true" aria-labelledby="purchase-modal-title"
            class="w-full max-w-xl bg-card rounded-lg shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-paddy-dark text-white">
                <h2 id="purchase-modal-title" class="font-display text-lg font-bold">Tambah Pembelian Gabah</h2>
                <button type="button" aria-label="Tutup" @click="open = false"
                    class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('purchases.store') }}" novalidate>
                @csrf

                <div class="px-5 py-5 space-y-4">
                    @if ($farmers->isEmpty() || $grains->isEmpty())
                        <p role="alert" class="px-3 py-2 rounded-md bg-wheat/20 text-ink text-xs">
                            Data petani atau jenis gabah masih kosong. Tambahkan dulu di menu Petani dan Jenis Barang (kategori Gabah).
                        </p>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="purchase_farmer" class="block text-sm font-medium mb-1">
                                Nama Petani <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <select id="purchase_farmer" name="farmer_id" x-model="form.farmer_id"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('farmer_id') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                                <option value="">Pilih petani</option>
                                @foreach ($farmers as $farmer)
                                    <option value="{{ $farmer->id }}">{{ $farmer->name }}</option>
                                @endforeach
                            </select>
                            @error('farmer_id')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_item" class="block text-sm font-medium mb-1">
                                Jenis Gabah <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <select id="purchase_item" name="item_type_id" x-model="form.item_type_id"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('item_type_id') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                                <option value="">Pilih jenis gabah</option>
                                @foreach ($grains as $grain)
                                    <option value="{{ $grain->id }}">{{ $grain->name }}</option>
                                @endforeach
                            </select>
                            @error('item_type_id')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="purchase_date" class="block text-sm font-medium mb-1">
                            Tanggal Pembelian <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <input id="purchase_date" name="purchase_date" type="date" x-model="form.purchase_date"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('purchase_date') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('purchase_date')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="purchase_weight" class="block text-sm font-medium mb-1">
                                Berat Gabah (kg) <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <input id="purchase_weight" name="weight_kg" type="number" step="0.01" min="0"
                                x-model="form.weight_kg" placeholder="0"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('weight_kg') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('weight_kg')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_price" class="block text-sm font-medium mb-1">
                                Harga per kg (Rp) <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <input id="purchase_price" name="price_per_kg" type="number" step="0.01" min="0"
                                x-model="form.price_per_kg" placeholder="0"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('price_per_kg') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('price_per_kg')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="purchase_moisture" class="block text-sm font-medium mb-1">
                                Potongan Kadar Air (%)
                            </label>
                            <input id="purchase_moisture" name="moisture_deduction" type="number" step="0.01" min="0"
                                x-model="form.moisture_deduction" placeholder="0 (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('moisture_deduction') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('moisture_deduction')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Pratinjau perhitungan --}}
                    <div class="rounded-md bg-paddy-soft px-4 py-3 text-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-paddy-dark/70 mb-2">Estimasi</p>
                        <div class="flex items-center justify-between">
                            <span class="text-muted">Berat bersih</span>
                            <span data-testid="net-weight" class="font-semibold text-paddy-dark" x-text="kg(netWeight)"></span>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-muted">Total bayar</span>
                            <span data-testid="total-price" class="font-display text-lg font-bold text-paddy-dark" x-text="rupiah(total)"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-line bg-paper/60">
                    <button type="button" @click="open = false"
                        class="px-4 py-2 rounded-md border border-line text-sm hover:bg-white transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection