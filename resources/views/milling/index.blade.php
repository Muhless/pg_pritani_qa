@extends('layouts.app')

@section('title', 'Proses Giling')

@section('content')
<div
    x-data="{
        open: @js($errors->any()),
        purchases: @js($purchaseWeights),
        minYield: {{ \App\Models\MillingProcess::MIN_NORMAL_YIELD }},
        maxYield: {{ \App\Models\MillingProcess::MAX_NORMAL_YIELD }},
        form: {
            purchase_id: @js(old('purchase_id', '')),
            rice_item_type_id: @js(old('rice_item_type_id', '')),
            processed_at: @js(old('processed_at', now()->toDateString())),
            rice_output_kg: @js(old('rice_output_kg', '')),
            bran_output_kg: @js(old('bran_output_kg', '')),
            husk_output_kg: @js(old('husk_output_kg', '')),
            broken_rice_output_kg: @js(old('broken_rice_output_kg', '')),
        },
        num(value) {
            return parseFloat(value) || 0;
        },
        get inputWeight() {
            const purchase = this.purchases[this.form.purchase_id];
            return purchase ? purchase.net : 0;
        },
        get totalOutput() {
            const total = this.num(this.form.rice_output_kg) + this.num(this.form.bran_output_kg)
                + this.num(this.form.husk_output_kg) + this.num(this.form.broken_rice_output_kg);
            return Math.round(total * 100) / 100;
        },
        get loss() {
            return Math.round((this.inputWeight - this.totalOutput) * 100) / 100;
        },
        get yieldPct() {
            if (!this.inputWeight) return 0;
            return Math.round(this.num(this.form.rice_output_kg) / this.inputWeight * 10000) / 100;
        },
        get yieldNormal() {
            return this.yieldPct >= this.minYield && this.yieldPct <= this.maxYield;
        },
        kg(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value) + ' kg';
        },
        pct(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value) + '%';
        },
        openAdd() {
            this.form = {
                purchase_id: '',
                rice_item_type_id: '',
                processed_at: '{{ now()->toDateString() }}',
                rice_output_kg: '',
                bran_output_kg: '',
                husk_output_kg: '',
                broken_rice_output_kg: '',
            };
            this.open = true;
        },
        confirmDelete(formEl, code) {
            Swal.fire({
                title: 'Hapus proses giling?',
                text: code + ' akan dihapus, stok hasil giling dikurangi dan stok gabah dikembalikan.',
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
            <h1 class="font-display text-3xl font-bold text-paddy-dark">Proses Giling</h1>
            <p class="mt-1 text-sm text-muted">{{ $processes->total() }} proses giling tercatat</p>
        </div>
        <button type="button" @click="openAdd()"
            class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Tambah Proses Giling
        </button>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div role="status" class="mb-4 px-4 py-3 rounded-md bg-paddy-soft text-paddy-dark text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('warning'))
        <div role="alert" class="mb-4 px-4 py-3 rounded-md bg-wheat/20 text-ink text-sm">
            {{ session('warning') }}
        </div>
    @endif
    @if (session('error'))
        <div role="alert" class="mb-4 px-4 py-3 rounded-md bg-clay/10 text-clay text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('milling.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 bg-card border border-line rounded-lg p-4">
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
        <a href="{{ route('milling.index') }}"
            class="px-4 py-2 rounded-md border border-line text-sm text-ink hover:bg-paper transition">
            Reset
        </a>
    </form>

    {{-- Tabel --}}
    <div class="bg-card border border-line rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-paddy-soft text-paddy-dark">
                <tr class="text-left">
                    <th scope="col" class="px-4 py-3">Kode</th>
                    <th scope="col" class="px-4 py-3">Tanggal</th>
                    <th scope="col" class="px-4 py-3">Pembelian</th>
                    <th scope="col" class="px-4 py-3 text-right">Gabah Masuk</th>
                    <th scope="col" class="px-4 py-3 text-right">Beras</th>
                    <th scope="col" class="px-4 py-3 text-right">Bekatul</th>
                    <th scope="col" class="px-4 py-3 text-right">Sekam</th>
                    <th scope="col" class="px-4 py-3 text-right">Menir</th>
                    <th scope="col" class="px-4 py-3 text-right">Rendemen</th>
                    <th scope="col" class="px-4 py-3 w-20 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($processes as $process)
                    @php
                        $code = 'GL-' . str_pad($process->id, 4, '0', STR_PAD_LEFT);
                        $purchaseCode = 'PB-' . str_pad($process->purchase_id, 4, '0', STR_PAD_LEFT);
                    @endphp
                    <tr class="border-t border-line hover:bg-paper/60">
                        <td class="px-4 py-3 font-mono text-xs">{{ $code }}</td>
                        <td class="px-4 py-3">{{ $process->processed_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs">{{ $purchaseCode }}</span>
                            <span class="block text-xs text-muted">{{ $process->purchase->farmer->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($process->input_weight_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($process->rice_output_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right">{{ number_format($process->bran_output_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right">{{ number_format($process->husk_output_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right">{{ number_format($process->broken_rice_output_kg, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold">{{ number_format($process->yield_percentage, 2, ',', '.') }}%</span>
                            @if ($process->isYieldNormal())
                                <span class="ml-1 inline-block px-2 py-0.5 rounded-full bg-paddy-soft text-paddy-dark text-xs">Normal</span>
                            @else
                                <span class="ml-1 inline-block px-2 py-0.5 rounded-full bg-wheat/20 text-clay text-xs">Di luar normal</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end">
                                <form method="POST" action="{{ route('milling.destroy', $process) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        aria-label="Hapus proses giling {{ $code }}"
                                        @click="confirmDelete($el.closest('form'), @js($code))"
                                        class="px-2.5 py-1 rounded border border-clay/30 text-xs text-clay hover:bg-clay/10 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-muted">Belum ada proses giling.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $processes->links() }}
    </div>

    {{-- Modal tambah --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4 overflow-y-auto"
        x-transition.opacity>
        <div role="dialog" aria-modal="true" aria-labelledby="milling-modal-title"
            class="w-full max-w-xl my-8 bg-card rounded-lg shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-paddy-dark text-white">
                <h2 id="milling-modal-title" class="font-display text-lg font-bold">Tambah Proses Giling</h2>
                <button type="button" aria-label="Tutup" @click="open = false"
                    class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('milling.store') }}" novalidate>
                @csrf

                <div class="px-5 py-5 space-y-4">
                    @if ($availablePurchases->isEmpty() || $riceTypes->isEmpty())
                        <div role="alert" class="px-3 py-2 rounded-md bg-wheat/20 text-ink text-xs space-y-1">
                            @if ($availablePurchases->isEmpty())
                                <p>Belum ada pembelian gabah yang siap digiling. Catat dulu di menu Pembelian Gabah.</p>
                            @endif
                            @if ($riceTypes->isEmpty())
                                <p>Belum ada jenis barang berkategori Beras. Tambahkan dulu di menu Jenis Barang.</p>
                            @endif
                        </div>
                    @endif

                    <div>
                        <label for="milling_purchase" class="block text-sm font-medium mb-1">
                            Pembelian Gabah <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <select id="milling_purchase" name="purchase_id" x-model="form.purchase_id"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('purchase_id') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                            <option value="">Pilih pembelian</option>
                            @foreach ($availablePurchases as $purchase)
                                <option value="{{ $purchase->id }}">
                                    PB-{{ str_pad($purchase->id, 4, '0', STR_PAD_LEFT) }} · {{ $purchase->farmer->name }} · {{ $purchase->itemType->name }} · {{ number_format($purchase->net_weight_kg, 2, ',', '.') }} kg
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_id')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-muted">
                            Gabah masuk (berat bersih):
                            <span data-testid="input-weight" class="font-semibold text-ink"
                                x-text="inputWeight ? kg(inputWeight) : '-'"></span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="milling_rice_type" class="block text-sm font-medium mb-1">
                                Jenis Beras Hasil <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <select id="milling_rice_type" name="rice_item_type_id" x-model="form.rice_item_type_id"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('rice_item_type_id') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                                <option value="">Pilih jenis beras</option>
                                @foreach ($riceTypes as $riceType)
                                    <option value="{{ $riceType->id }}">{{ $riceType->name }}</option>
                                @endforeach
                            </select>
                            @error('rice_item_type_id')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="milling_date" class="block text-sm font-medium mb-1">
                                Tanggal Giling <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <input id="milling_date" name="processed_at" type="date" x-model="form.processed_at"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('processed_at') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('processed_at')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="milling_rice" class="block text-sm font-medium mb-1">
                                Hasil Beras (kg) <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <input id="milling_rice" name="rice_output_kg" type="number" step="0.01" min="0"
                                x-model="form.rice_output_kg" placeholder="0"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('rice_output_kg') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('rice_output_kg')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="milling_bran" class="block text-sm font-medium mb-1">Hasil Bekatul (kg)</label>
                            <input id="milling_bran" name="bran_output_kg" type="number" step="0.01" min="0"
                                x-model="form.bran_output_kg" placeholder="0 (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('bran_output_kg') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('bran_output_kg')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="milling_husk" class="block text-sm font-medium mb-1">Hasil Sekam (kg)</label>
                            <input id="milling_husk" name="husk_output_kg" type="number" step="0.01" min="0"
                                x-model="form.husk_output_kg" placeholder="0 (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('husk_output_kg') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('husk_output_kg')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="milling_broken" class="block text-sm font-medium mb-1">Hasil Menir (kg)</label>
                            <input id="milling_broken" name="broken_rice_output_kg" type="number" step="0.01" min="0"
                                x-model="form.broken_rice_output_kg" placeholder="0 (opsional)"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('broken_rice_output_kg') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('broken_rice_output_kg')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @error('total_output')
                        <p role="alert" class="px-3 py-2 rounded-md bg-clay/10 text-xs text-clay">{{ $message }}</p>
                    @enderror

                    {{-- Pratinjau perhitungan --}}
                    <div class="rounded-md bg-paddy-soft px-4 py-3 text-sm space-y-1">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-paddy-dark/70 mb-2">Estimasi</p>
                        <div class="flex items-center justify-between">
                            <span class="text-muted">Total hasil</span>
                            <span data-testid="total-output" class="font-semibold text-paddy-dark" x-text="kg(totalOutput)"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted">Susut</span>
                            <span data-testid="loss" class="font-semibold text-paddy-dark"
                                x-text="inputWeight ? kg(loss) : '-'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted">Rendemen</span>
                            <span data-testid="yield-preview" class="font-display text-lg font-bold text-paddy-dark"
                                x-text="inputWeight ? pct(yieldPct) : '-'"></span>
                        </div>
                        <p data-testid="yield-status" class="text-xs font-medium"
                            x-show="inputWeight && num(form.rice_output_kg) > 0"
                            :class="yieldNormal ? 'text-paddy-dark' : 'text-clay'"
                            x-text="yieldNormal ? 'Normal' : 'Di luar rentang normal (' + minYield + '–' + maxYield + '%)'"></p>
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