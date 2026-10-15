@extends('layouts.app')

@section('title', 'Jenis Barang')

@section('content')
<div
    x-data="{
        open: @js($errors->any()),
        editId: @js(old('editing_id')),
        form: {
            name: @js(old('name', '')),
            category: @js(old('category', 'grain')),
            unit: @js(old('unit', 'kg')),
        },
        openAdd() {
            this.editId = null;
            this.form = { name: '', category: 'grain', unit: 'kg' };
            this.open = true;
        },
        openEdit(item) {
            this.editId = item.id;
            this.form = {
                name: item.name ?? '',
                category: item.category ?? 'grain',
                unit: item.unit ?? 'kg',
            };
            this.open = true;
        },
        confirmDelete(formEl, name) {
            Swal.fire({
                title: 'Hapus jenis barang?',
                text: name + ' akan dihapus.',
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
            <h1 class="font-display text-3xl font-bold text-paddy-dark">Jenis Barang</h1>
            <p class="mt-1 text-sm text-muted">{{ $itemTypes->total() }} jenis barang terdaftar</p>
        </div>
        <button type="button" @click="openAdd()"
            class="px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Tambah Jenis Barang
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
    <form method="GET" action="{{ route('item-types.index') }}"
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
        <button type="submit"
            class="px-4 py-2 rounded-md bg-paddy-dark text-white text-sm font-medium hover:bg-paddy-deep transition">
            Terapkan
        </button>
        <a href="{{ route('item-types.index') }}"
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
                    <th scope="col" class="px-4 py-3">Kategori</th>
                    <th scope="col" class="px-4 py-3">Satuan</th>
                    <th scope="col" class="px-4 py-3 w-28 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($itemTypes as $item)
                    <tr class="border-t border-line hover:bg-paper/60">
                        <td class="px-4 py-3 text-muted">{{ $itemTypes->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full bg-paddy-soft text-paddy-dark text-xs font-medium">
                                {{ \App\Models\ItemType::CATEGORIES[$item->category] ?? $item->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $item->unit }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button"
                                    aria-label="Edit {{ $item->name }}"
                                    @click="openEdit(@js($item->only(['id', 'name', 'category', 'unit'])))"
                                    class="px-2.5 py-1 rounded border border-line text-xs text-paddy-dark hover:bg-paddy-soft transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('item-types.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                        aria-label="Hapus {{ $item->name }}"
                                        @click="confirmDelete($el.closest('form'), @js($item->name))"
                                        class="px-2.5 py-1 rounded border border-clay/30 text-xs text-clay hover:bg-clay/10 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-muted">Belum ada data jenis barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $itemTypes->links() }}
    </div>

    {{-- Modal tambah / edit --}}
    <div x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 px-4"
        x-transition.opacity>
        <div role="dialog" aria-modal="true" aria-labelledby="item-modal-title"
            class="w-full max-w-lg bg-card rounded-lg shadow-xl overflow-hidden">

            <div class="flex items-center justify-between px-5 py-4 bg-paddy-dark text-white">
                <h2 id="item-modal-title" class="font-display text-lg font-bold"
                    x-text="editId ? 'Edit Jenis Barang' : 'Tambah Jenis Barang'"></h2>
                <button type="button" aria-label="Tutup" @click="open = false"
                    class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
            </div>

            <form method="POST" novalidate
                :action="editId ? '{{ url('item-types') }}/' + editId : '{{ route('item-types.store') }}'">
                @csrf
                <input type="hidden" name="_method" value="PUT" :disabled="!editId">
                <input type="hidden" name="editing_id" :value="editId" :disabled="!editId">

                <div class="px-5 py-5 space-y-4">
                    <div>
                        <label for="item_name" class="block text-sm font-medium mb-1">
                            Nama Barang <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <input id="item_name" name="name" type="text" x-model="form.name"
                            placeholder="Contoh: IR64, Pandan Wangi"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('name') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('name')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="item_category" class="block text-sm font-medium mb-1">
                            Kategori Barang <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <select id="item_category" name="category" x-model="form.category"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('category') border-clay @else border-line @enderror bg-white focus:outline-none focus:border-paddy">
                            @foreach (\App\Models\ItemType::CATEGORIES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="item_unit" class="block text-sm font-medium mb-1">
                            Satuan <span aria-hidden="true" class="text-clay">*</span>
                        </label>
                        <input id="item_unit" name="unit" type="text" x-model="form.unit"
                            placeholder="kg"
                            class="w-full px-3 py-2 text-sm rounded-md border @error('unit') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        @error('unit')
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