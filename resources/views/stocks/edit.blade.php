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
               <input id="note" name="note" type="text" value="{{ old('note') }}" maxlength="255"
                    placeholder="Contoh: koreksi susut, hasil timbangan ulang"
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

     {{-- Riwayat perubahan stok --}}
     <div class="mt-8 max-w-2xl" data-testid="stock-history">
          <h2 class="font-display text-xl font-bold text-paddy-dark">Riwayat Perubahan Stok</h2>
          <p class="mt-1 mb-4 text-sm text-muted">20 perubahan terakhir untuk {{ $item->name }}.</p>

          <div class="bg-card border border-line rounded-lg overflow-hidden">
               <table class="w-full text-sm">
                    <thead class="bg-paddy-soft text-paddy-dark">
                         <tr class="text-left">
                              <th scope="col" class="px-4 py-3">Tanggal</th>
                              <th scope="col" class="px-4 py-3">Jenis</th>
                              <th scope="col" class="px-4 py-3 text-right">Jumlah</th>
                              <th scope="col" class="px-4 py-3 text-right">Stok</th>
                              <th scope="col" class="px-4 py-3">Catatan</th>
                              <th scope="col" class="px-4 py-3">Oleh</th>
                         </tr>
                    </thead>
                    <tbody>
                         @forelse ($movements as $movement)
                              <tr class="border-t border-line hover:bg-paper/60">
                                   <td class="px-4 py-3 whitespace-nowrap text-muted">
                                        {{ $movement->created_at->format('d/m/Y H:i') }}
                                   </td>
                                   <td class="px-4 py-3">
                                        <span
                                             class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $movement->type === 'in' ? 'bg-paddy-soft text-paddy-dark' : 'bg-clay/10 text-clay' }}">
                                             {{ \App\Models\StockMovement::TYPES[$movement->type] ?? $movement->type }}
                                        </span>
                                   </td>
                                   <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">
                                        {{ $movement->type === 'in' ? '+' : ($movement->type === 'out' ? '−' : '') }}{{ number_format((float) $movement->amount, 2, ',', '.') }}
                                        {{ $item->unit }}
                                   </td>
                                   <td class="px-4 py-3 text-right whitespace-nowrap text-muted">
                                        {{ number_format((float) $movement->quantity_before, 2, ',', '.') }}
                                        &rarr;
                                        {{ number_format((float) $movement->quantity_after, 2, ',', '.') }}
                                   </td>
                                   <td class="px-4 py-3">{{ $movement->note ?: '—' }}</td>
                                   <td class="px-4 py-3 text-muted">{{ $movement->user->name ?? '—' }}</td>
                              </tr>
                         @empty
                              <tr>
                                   <td colspan="6" class="px-4 py-10 text-center text-muted">
                                        Belum ada perubahan stok yang tercatat.
                                   </td>
                              </tr>
                         @endforelse
                    </tbody>
               </table>
          </div>
     </div>
@endsection