@extends('layouts.app')

@section('title', 'Update Stok')

@section('content')
     <div class="mb-6">
          <h1 class="font-display text-3xl font-bold text-paddy-dark">Update Stok</h1>
          <p class="mt-1 text-sm text-muted">{{ $item->name }} · stok saat ini
               {{ number_format((float) $item->quantity_kg, 2, ',', '.') }} {{ $item->unit }}
          </p>
     </div>

     <form method="POST" action="{{ route('stocks.update', $item) }}"
          class="max-w-md space-y-4 bg-card border border-line rounded-lg p-6">
          @csrf
          @method('PUT')

          <div>
               <label for="type" class="block text-xs font-medium text-muted mb-1">Jenis perubahan</label>
               <select id="type" name="type"
                    class="w-full px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
                    <option value="in" @selected(old('type') === 'in')>Tambah stok (masuk)</option>
                    <option value="out" @selected(old('type') === 'out')>Kurangi stok (keluar)</option>
                    <option value="set" @selected(old('type') === 'set')>Set ke jumlah tertentu (koreksi)</option>
               </select>
               @error('type')
               <p class="mt-1 text-xs text-clay">{{ $message }}</p> @enderror
          </div>

          <div>
               <label for="amount" class="block text-xs font-medium text-muted mb-1">Jumlah ({{ $item->unit }})</label>
               <input id="amount" name="amount" type="number" step="0.01" min="0" value="{{ old('amount') }}"
                    class="w-full px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
               @error('amount')
               <p class="mt-1 text-xs text-clay">{{ $message }}</p> @enderror
          </div>

          <div>
               <label for="note" class="block text-xs font-medium text-muted mb-1">Catatan (opsional)</label>
               <input id="note" name="note" type="text" value="{{ old('note') }}"
                    class="w-full px-3 py-2 text-sm rounded-md border border-line bg-white focus:outline-none focus:border-paddy">
               @error('note')
               <p class="mt-1 text-xs text-clay">{{ $message }}</p> @enderror
          </div>

          <div class="flex gap-2">
               <button type="submit" data-testid="stock-submit"
                    class="px-4 py-2 rounded-md bg-paddy-dark text-white text-sm font-medium hover:bg-paddy-deep transition">
                    Simpan
               </button>
               <a href="{{ route('stocks.index') }}"
                    class="px-4 py-2 rounded-md border border-line text-sm text-ink hover:bg-paper transition">
                    Batal
               </a>
          </div>
     </form>
@endsection