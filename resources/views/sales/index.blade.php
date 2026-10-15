@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
<div
    x-data="{
        open: @js($errors->any()),
        errors: @js($errors->getMessages()),
        stocks: @js($stockMap),
        maxItems: 5,
        form: {
            customer_id: @js(old('customer_id', '')),
            sale_date: @js(old('sale_date', now()->toDateString())),
            payment_method: @js(old('payment_method', 'cash')),
            due_date: @js(old('due_date', '')),
            items: @js(old('items', [['item_type_id' => '', 'quantity_kg' => '', 'price_per_kg' => '']])),
        },
        err(key) {
            return this.errors[key] ? this.errors[key][0] : '';
        },
        stockOf(id) {
            return this.stocks[id] ?? 0;
        },
        lineSubtotal(item) {
            const qty = parseFloat(item.quantity_kg) || 0;
            const price = parseFloat(item.price_per_kg) || 0;
            return Math.round(qty * price * 100) / 100;
        },
        get total() {
            const sum = this.form.items.reduce((acc, item) => acc + this.lineSubtotal(item), 0);
            return Math.round(sum * 100) / 100;
        },
        kg(value) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value) + ' kg';
        },
        rupiah(value) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
        },
        addItem() {
            if (this.form.items.length < this.maxItems) {
                this.form.items.push({ item_type_id: '', quantity_kg: '', price_per_kg: '' });
            }
        },
        removeItem(index) {
            this.errors = {};
            this.form.items.splice(index, 1);
        },
        openAdd() {
            this.errors = {};
            this.form = {
                customer_id: '',
                sale_date: '{{ now()->toDateString() }}',
                payment_method: 'cash',
                due_date: '',
                items: [{ item_type_id: '', quantity_kg: '', price_per_kg: '' }],
            };
            this.open = true;
        },
        confirmDelete(formEl, invoice) {
            Swal.fire({
                title: 'Hapus penjualan?',
                text: invoice + ' akan dihapus dan stok barang dikembalikan.',
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
        confirmPay(formEl, invoice) {
            Swal.fire({
                title: 'Tandai lunas?',
                text: invoice + ' akan ditandai sebagai lunas.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3f7d3a',
                cancelButtonColor: '#6b7c68',
                confirmButtonText: 'Ya, Lunas',
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
            <h1 class="font-display text-3xl font-bold text-paddy-dark">Penjualan</h1>
            <p class="mt-1 text-sm text-muted">{{ $sales->total() }} transaksi penjualan</p>
        </div>
        <button type="button" @click="openAdd()"
            class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Tambah Penjualan
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
    <form method="GET" action="{{ route('sales.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 bg-card border border-line rounded-lg p-4">
        <div>
            <label for="filter_customer" class="block text-xs font-medium text-muted mb-1">Saring pelanggan</label>
            <select id="filter_customer" name="customer_id"
                class="w-48 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                <option value="">Semua Pelanggan</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string) request('customer_id') === (string) $customer->id)>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter_method" class="block text-xs font-medium text-muted mb-1">Saring metode</label>
            <select id="filter_method" name="payment_method"
                class="w-40 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                <option value="">Semua Metode</option>
                @foreach (\App\Models\Sale::PAYMENT_METHODS as $value => $label)
                    <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="filter_status" class="block text-xs font-medium text-muted mb-1">Saring status</label>
            <select id="filter_status" name="payment_status"
                class="w-40 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                <option value="">Semua Status</option>
                <option value="paid" @selected(request('payment_status') === 'paid')>Lunas</option>
                <option value="unpaid" @selected(request('payment_status') === 'unpaid')>Belum Lunas</option>
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
        <a href="{{ route('sales.index') }}"
            class="px-4 py-2 rounded-md border border-line text-sm text-ink hover:bg-paper transition">
            Reset
        </a>
    </form>

    {{-- Tabel --}}
    <div class="bg-card border border-line rounded-lg overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-paddy-soft text-paddy-dark">
                <tr class="text-left">
                    <th scope="col" class="px-4 py-3">Invoice</th>
                    <th scope="col" class="px-4 py-3">Tanggal</th>
                    <th scope="col" class="px-4 py-3">Pelanggan</th>
                    <th scope="col" class="px-4 py-3">Barang</th>
                    <th scope="col" class="px-4 py-3">Pembayaran</th>
                    <th scope="col" class="px-4 py-3">Status</th>
                    <th scope="col" class="px-4 py-3 text-right">Total</th>
                    <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr class="border-t border-line hover:bg-paper/60 align-top">
                        <td class="px-4 py-3 font-mono text-xs">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3">{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $sale->customer->name }}</td>
                        <td class="px-4 py-3">
                            <ul class="space-y-0.5">
                                @foreach ($sale->items as $item)
                                    <li class="text-xs">
                                        <span class="font-medium">{{ $item->itemType->name }}</span>
                                        · {{ number_format($item->quantity_kg, 2, ',', '.') }} kg
                                        × Rp {{ number_format($item->price_per_kg, 0, ',', '.') }}
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-3">{{ \App\Models\Sale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method }}</td>
                        <td class="px-4 py-3">
                            @if ($sale->isUnpaid())
                                <span class="inline-block px-2 py-0.5 rounded-full bg-wheat/20 text-clay text-xs font-medium">Belum lunas</span>
                                @if ($sale->due_date)
                                    <span class="block mt-1 text-xs {{ $sale->isOverdue() ? 'text-clay font-semibold' : 'text-muted' }}">
                                        Jatuh tempo {{ $sale->due_date->format('d/m/Y') }}{{ $sale->isOverdue() ? ' · Terlambat' : '' }}
                                    </span>
                                @endif
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full bg-paddy-soft text-paddy-dark text-xs font-medium">Lunas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($sale->isUnpaid())
                                    <form method="POST" action="{{ route('sales.pay', $sale) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button"
                                            aria-label="Tandai lunas {{ $sale->invoice_number }}"
                                            @click="confirmPay($el.closest('form'), @js($sale->invoice_number))"
                                            class="px-2.5 py-1 rounded border border-line text-xs text-paddy-dark hover:bg-paddy-soft transition">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('sales.destroy', $sale) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        aria-label="Hapus penjualan {{ $sale->invoice_number }}"
                                        @click="confirmDelete($el.closest('form'), @js($sale->invoice_number))"
                                        class="px-2.5 py-1 rounded border border-clay/30 text-xs text-clay hover:bg-clay/10 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-muted">Belum ada data penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sales->links() }}
    </div>

    {{-- Modal tambah --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-start justify-center bg-ink/50 px-4 overflow-y-auto"
        x-transition.opacity>
        <div role="dialog" aria-modal="true" aria-labelledby="sale-modal-title"
            class="w-full max-w-3xl my-8 bg-card rounded-lg shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-paddy-dark text-white">
                <h2 id="sale-modal-title" class="font-display text-lg font-bold">Tambah Penjualan</h2>
                <button type="button" aria-label="Tutup" @click="open = false"
                    class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('sales.store') }}" novalidate>
                @csrf

                <div class="px-5 py-5 space-y-4">
                    @if ($customers->isEmpty() || $itemGroups->isEmpty())
                        <div role="alert" class="px-3 py-2 rounded-md bg-wheat/20 text-ink text-xs space-y-1">
                            @if ($customers->isEmpty())
                                <p>Belum ada pelanggan. Tambahkan dulu di menu Pelanggan.</p>
                            @endif
                            @if ($itemGroups->isEmpty())
                                <p>Belum ada barang yang bisa dijual (beras, bekatul, sekam, menir). Tambahkan di menu Jenis Barang atau proses giling dulu.</p>
                            @endif
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sale_customer" class="block text-sm font-medium mb-1">
                                Nama Pelanggan <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <select id="sale_customer" name="customer_id" x-model="form.customer_id"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('customer_id') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                                <option value="">Pilih pelanggan</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sale_date" class="block text-sm font-medium mb-1">
                                Tanggal Penjualan <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <input id="sale_date" name="sale_date" type="date" x-model="form.sale_date"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('sale_date') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('sale_date')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sale_method" class="block text-sm font-medium mb-1">
                                Metode Pembayaran <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <select id="sale_method" name="payment_method" x-model="form.payment_method"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('payment_method') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                                @foreach (\App\Models\Sale::PAYMENT_METHODS as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="form.payment_method === 'credit'" x-cloak>
                            <label for="sale_due_date" class="block text-sm font-medium mb-1">
                                Jatuh Tempo <span aria-hidden="true" class="text-clay">*</span>
                            </label>
                            <input id="sale_due_date" name="due_date" type="date" x-model="form.due_date"
                                class="w-full px-3 py-2 text-sm rounded-md border @error('due_date') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                            @error('due_date')
                                <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Daftar barang --}}
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-ink">Daftar Barang</p>

                        <template x-for="(item, index) in form.items" :key="index">
                            <div class="rounded-md border border-line p-3 space-y-2">
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-5">
                                        <label :for="'sale_item_' + index" class="block text-sm font-medium mb-1"
                                            x-text="'Barang ke-' + (index + 1)"></label>
                                        <select :id="'sale_item_' + index" :name="'items[' + index + '][item_type_id]'"
                                            x-model="item.item_type_id"
                                            class="w-full px-3 py-2 text-sm rounded-md border bg-white focus:outline-none focus:border-paddy"
                                            :class="err('items.' + index + '.item_type_id') ? 'border-clay' : 'border-line'">
                                            <option value="">Pilih barang</option>
                                            @foreach (\App\Models\Sale::SELLABLE_CATEGORIES as $category)
                                                @if ($itemGroups->has($category))
                                                    <optgroup label="{{ \App\Models\ItemType::CATEGORIES[$category] }}">
                                                        @foreach ($itemGroups[$category] as $itemType)
                                                            <option value="{{ $itemType->id }}">{{ $itemType->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            @endforeach
                                        </select>
                                        <p role="alert" class="mt-1 text-xs text-clay"
                                            x-show="err('items.' + index + '.item_type_id')"
                                            x-text="err('items.' + index + '.item_type_id')"></p>
                                    </div>

                                    <div class="col-span-3">
                                        <label :for="'sale_qty_' + index" class="block text-sm font-medium mb-1"
                                            x-text="'Berat ke-' + (index + 1) + ' (kg)'"></label>
                                        <input :id="'sale_qty_' + index" :name="'items[' + index + '][quantity_kg]'"
                                            type="number" step="0.01" min="0" placeholder="0"
                                            x-model="item.quantity_kg"
                                            class="w-full px-3 py-2 text-sm rounded-md border focus:outline-none focus:border-paddy"
                                            :class="err('items.' + index + '.quantity_kg') ? 'border-clay' : 'border-line'">
                                        <p role="alert" class="mt-1 text-xs text-clay"
                                            x-show="err('items.' + index + '.quantity_kg')"
                                            x-text="err('items.' + index + '.quantity_kg')"></p>
                                    </div>

                                    <div class="col-span-3">
                                        <label :for="'sale_price_' + index" class="block text-sm font-medium mb-1"
                                            x-text="'Harga ke-' + (index + 1) + ' (Rp/kg)'"></label>
                                        <input :id="'sale_price_' + index" :name="'items[' + index + '][price_per_kg]'"
                                            type="number" step="0.01" min="0" placeholder="0"
                                            x-model="item.price_per_kg"
                                            class="w-full px-3 py-2 text-sm rounded-md border focus:outline-none focus:border-paddy"
                                            :class="err('items.' + index + '.price_per_kg') ? 'border-clay' : 'border-line'">
                                        <p role="alert" class="mt-1 text-xs text-clay"
                                            x-show="err('items.' + index + '.price_per_kg')"
                                            x-text="err('items.' + index + '.price_per_kg')"></p>
                                    </div>

                                    <div class="col-span-1 flex items-end justify-end">
                                        <button type="button"
                                            x-show="form.items.length > 1"
                                            @click="removeItem(index)"
                                            :aria-label="'Hapus baris ke-' + (index + 1)"
                                            class="h-9 w-9 rounded-md border border-clay/30 text-clay hover:bg-clay/10 transition text-lg leading-none">&times;</button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-xs text-muted">
                                    <span :data-testid="'stock-hint-' + (index + 1)"
                                        x-text="item.item_type_id ? 'Stok tersedia: ' + kg(stockOf(item.item_type_id)) : ''"></span>
                                    <span>
                                        Subtotal:
                                        <span :data-testid="'subtotal-' + (index + 1)" class="font-semibold text-ink"
                                            x-text="rupiah(lineSubtotal(item))"></span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <p role="alert" class="px-3 py-2 rounded-md bg-clay/10 text-xs text-clay"
                            x-show="err('items')" x-text="err('items')"></p>

                        <button type="button" @click="addItem()" :disabled="form.items.length >= maxItems"
                            class="px-3 py-2 rounded-md border border-line text-sm text-paddy-dark hover:bg-paddy-soft transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Tambah Barang
                        </button>
                    </div>

                    {{-- Pratinjau total --}}
                    <div class="rounded-md bg-paddy-soft px-4 py-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-widest text-paddy-dark/70">Total penjualan</span>
                            <span data-testid="sale-total" class="font-display text-xl font-bold text-paddy-dark"
                                x-text="rupiah(total)"></span>
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