@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
<div
    x-data="{
        open: @js($errors->any()),
        editId: @js(old('editing_id')),
        form: {
            name: @js(old('name', '')),
            type: @js(old('type', 'individual')),
            phone: @js(old('phone', '')),
            address: @js(old('address', '')),
        },
        openAdd() {
            this.editId = null;
            this.form = { name: '', type: 'individual', phone: '', address: '' };
            this.open = true;
        },
        openEdit(customer) {
            this.editId = customer.id;
            this.form = {
                name: customer.name ?? '',
                type: customer.type ?? 'individual',
                phone: customer.phone ?? '',
                address: customer.address ?? '',
            };
            this.open = true;
        },
        confirmDelete(formEl, name) {
            Swal.fire({
                title: 'Hapus pelanggan?',
                text: 'Data ' + name + ' akan dihapus.',
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
            <h1 class="font-display text-3xl font-bold text-paddy-dark">Pelanggan</h1>
            <p class="mt-1 text-sm text-muted">{{ $customers->total() }} pelanggan terdaftar</p>
        </div>
        <button type="button" @click="openAdd()"
            class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Tambah Pelanggan
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

    {{-- Pencarian & filter --}}
    <form method="GET" action="{{ route('customers.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 bg-card border border-line rounded-lg p-4">
        <div>
            <label for="search" class="block text-xs font-medium text-muted mb-1">Cari nama</label>
            <input id="search" name="search" type="text" value="{{ request('search') }}"
                placeholder="Ketik nama pelanggan..."
                class="w-64 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
        </div>
        <div>
            <label for="filter_type" class="block text-xs font-medium text-muted mb-1">Saring jenis</label>
            <select id="filter_type" name="type"
                class="w-48 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                <option value="">Semua Jenis</option>
                @foreach (\App\Models\Customer::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
            class="px-4 py-2 rounded-md bg-paddy-dark text-white text-sm font-medium hover:bg-paddy-deep transition">
            Terapkan
        </button>
        <a href="{{ route('customers.index') }}"
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
                    <th scope="col" class="px-4 py-3">Nama</th>
                    <th scope="col" class="px-4 py-3">Jenis</th>
                    <th scope="col" class="px-4 py-3">Telepon</th>
                    <th scope="col" class="px-4 py-3">Alamat</th>
                    <th scope="col" class="px-4 py-3 w-28 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr class="border-t border-line hover:bg-paper/60">
                        <td class="px-4 py-3 text-muted">{{ $customers->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $customer->name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-paddy-soft text-paddy-dark text-xs font-medium">
                                {{ \App\Models\Customer::TYPES[$customer->type] ?? $customer->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-muted">{{ $customer->address ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    aria-label="Edit {{ $customer->name }}"
                                    @click="openEdit(@js($customer->only(['id', 'name', 'type', 'phone', 'address'])))"
                                    class="px-2.5 py-1 rounded border border-line text-xs text-paddy-dark hover:bg-paddy-soft transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        aria-label="Hapus {{ $customer->name }}"
                                        @click="confirmDelete($el.closest('form'), @js($customer->name))"
                                        class="px-2.5 py-1 rounded border border-clay/30 text-xs text-clay hover:bg-clay/10 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-muted">Belum ada data pelanggan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $customers->links() }}
    </div>

    {{-- Modal tambah / edit --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4"
        x-transition.opacity>
        <div role="dialog" aria-modal="true" aria-labelledby="customer-modal-title"
            class="w-full max-w-lg bg-card rounded-lg shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-paddy-dark text-white">
                <h2 id="customer-modal-title" class="font-display text-lg font-bold"
                    x-text="editId ? 'Edit Pelanggan' : 'Tambah Pelanggan'"></h2>
                <button type="button" aria-label="Tutup" @click="open = false"
                    class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form method="POST" novalidate
                :action="editId ? '{{ url('customers') }}/' + editId : '{{ route('customers.store') }}'">
                @csrf
                <input type="hidden" name="_method" value="PUT" :disabled="!editId">
                <input type="hidden" name="editing_id" :value="editId" :disabled="!editId">

                <div class="px-5 py-5 space-y-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium mb-1">
                            Nama Pelanggan <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <input id="customer_name" name="name" type="text" x-model="form.name"
                            placeholder="Masukkan nama pelanggan"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('name') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('name')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_type" class="block text-sm font-medium mb-1">
                            Jenis Pelanggan <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <select id="customer_type" name="type" x-model="form.type"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('type') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                            @foreach (\App\Models\Customer::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-sm font-medium mb-1">Nomor Telepon</label>
                        <input id="customer_phone" name="phone" type="text" inputmode="tel" x-model="form.phone"
                            placeholder="08xxxxxxxxxx (opsional)"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('phone') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('phone')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_address" class="block text-sm font-medium mb-1">Alamat</label>
                        <textarea id="customer_address" name="address" rows="3" x-model="form.address"
                            placeholder="Alamat pelanggan (opsional)"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('address') border-clay @else border-line @enderror focus:outline-none focus:border-paddy resize-none"></textarea>
                        @error('address')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
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