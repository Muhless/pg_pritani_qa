@extends('layouts.app')

@section('title', 'Petani')

@section('content')
<div
    x-data="{
        open: @js($errors->any()),
        editId: @js(old('editing_id')),
        form: {
            name: @js(old('name', '')),
            nik: @js(old('nik', '')),
            phone: @js(old('phone', '')),
            address: @js(old('address', '')),
        },
        openAdd() {
            this.editId = null;
            this.form = { name: '', nik: '', phone: '', address: '' };
            this.open = true;
        },
        openEdit(farmer) {
            this.editId = farmer.id;
            this.form = {
                name: farmer.name ?? '',
                nik: farmer.nik ?? '',
                phone: farmer.phone ?? '',
                address: farmer.address ?? '',
            };
            this.open = true;
        },
        confirmDelete(formEl, name) {
            Swal.fire({
                title: 'Hapus petani?',
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
            <h1 class="font-display text-3xl font-bold text-paddy-dark">Petani</h1>
            <p class="mt-1 text-sm text-muted">{{ $farmers->total() }} petani terdaftar</p>
        </div>
        <button type="button" @click="openAdd()"
            class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Tambah Petani
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

    {{-- Pencarian --}}
    <form method="GET" action="{{ route('farmers.index') }}"
        class="mb-4 flex items-end gap-3 bg-card border border-line rounded-lg p-4">
        <div>
            <label for="search" class="block text-xs font-medium text-muted mb-1">Cari nama</label>
            <input id="search" name="search" type="text" value="{{ request('search') }}"
                placeholder="Ketik nama petani..."
                class="w-64 px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
        </div>
        <button type="submit"
            class="px-4 py-2 rounded-md bg-paddy-dark text-white text-sm font-medium hover:bg-paddy-deep transition">
            Cari
        </button>
        <a href="{{ route('farmers.index') }}"
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
                    <th scope="col" class="px-4 py-3">NIK</th>
                    <th scope="col" class="px-4 py-3">Telepon</th>
                    <th scope="col" class="px-4 py-3">Alamat</th>
                    <th scope="col" class="px-4 py-3 w-28 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($farmers as $farmer)
                    <tr class="border-t border-line hover:bg-paper/60">
                        <td class="px-4 py-3 text-muted">{{ $farmers->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $farmer->name }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $farmer->nik ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $farmer->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-muted">{{ $farmer->address ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    aria-label="Edit {{ $farmer->name }}"
                                    @click="openEdit(@js($farmer->only(['id', 'name', 'nik', 'phone', 'address'])))"
                                    class="px-2.5 py-1 rounded border border-line text-xs text-paddy-dark hover:bg-paddy-soft transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('farmers.destroy', $farmer) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        aria-label="Hapus {{ $farmer->name }}"
                                        @click="confirmDelete($el.closest('form'), @js($farmer->name))"
                                        class="px-2.5 py-1 rounded border border-clay/30 text-xs text-clay hover:bg-clay/10 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-muted">Belum ada data petani.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $farmers->links() }}
    </div>

    {{-- Modal tambah / edit --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4"
        x-transition.opacity>
        <div role="dialog" aria-modal="true" aria-labelledby="farmer-modal-title"
            class="w-full max-w-lg bg-card rounded-lg shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-paddy-dark text-white">
                <h2 id="farmer-modal-title" class="font-display text-lg font-bold"
                    x-text="editId ? 'Edit Petani' : 'Tambah Petani'"></h2>
                <button type="button" aria-label="Tutup" @click="open = false"
                    class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form method="POST" novalidate
                :action="editId ? '{{ url('farmers') }}/' + editId : '{{ route('farmers.store') }}'">
                @csrf
                <input type="hidden" name="_method" value="PUT" :disabled="!editId">
                <input type="hidden" name="editing_id" :value="editId" :disabled="!editId">

                <div class="px-5 py-5 space-y-4">
                    <div>
                        <label for="farmer_name" class="block text-sm font-medium mb-1">
                            Nama Lengkap <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <input id="farmer_name" name="name" type="text" x-model="form.name"
                            placeholder="Masukkan nama lengkap petani"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('name') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('name')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farmer_nik" class="block text-sm font-medium mb-1">NIK</label>
                        <input id="farmer_nik" name="nik" type="text" inputmode="numeric" x-model="form.nik"
                            placeholder="16 digit angka (opsional)"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('nik') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('nik')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farmer_phone" class="block text-sm font-medium mb-1">Nomor Telepon</label>
                        <input id="farmer_phone" name="phone" type="text" inputmode="tel" x-model="form.phone"
                            placeholder="08xxxxxxxxxx (opsional)"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('phone') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('phone')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farmer_address" class="block text-sm font-medium mb-1">Alamat</label>
                        <textarea id="farmer_address" name="address" rows="3" x-model="form.address"
                            placeholder="Alamat petani (opsional)"
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
@endsection<div>
    <!-- Smile, breathe, and go slowly. - Thich Nhat Hanh -->
</div>
