@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
     <div class="mb-8">
          <h1 class="font-display text-3xl font-bold text-paddy-dark">Ringkasan Hari Ini</h1>
          <p class="mt-1 text-sm text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
     </div>

     @php
          $stats = [
               ['label' => 'Gabah Masuk', 'value' => '0 kg', 'bg' => 'bg-paddy-deep'],
               ['label' => 'Stok Beras', 'value' => '0 kg', 'bg' => 'bg-paddy-dark'],
               ['label' => 'Penjualan', 'value' => '0', 'bg' => 'bg-paddy'],
               ['label' => 'Rendemen Rata-rata', 'value' => '0%', 'bg' => 'bg-leaf'],
          ];
         @endphp

     <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          @foreach ($stats as $stat)
               <div class="{{ $stat['bg'] }} text-white rounded-lg p-5 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">{{ $stat['label'] }}</p>
                    <p class="mt-2 font-display text-3xl font-bold">{{ $stat['value'] }}</p>
               </div>
          @endforeach
     </div>

     <div class="mt-8 bg-card border border-line rounded-lg p-6">
          <h2 class="text-sm font-semibold text-ink mb-4">Alur Produksi</h2>
          <div class="flex flex-wrap items-center gap-3 text-sm">
               <span class="px-4 py-2 rounded-md bg-paddy-soft text-paddy-dark font-medium">Gabah</span>
               <span class="text-muted">→</span>
               <span class="px-4 py-2 rounded-md bg-paddy-soft text-paddy-dark font-medium">Penggilingan</span>
               <span class="text-muted">→</span>
               <span class="px-4 py-2 rounded-md bg-paddy text-white font-medium">Beras</span>
               <span class="text-muted">+</span>
               <span class="px-3 py-2 rounded-md border border-line text-muted">Bekatul</span>
               <span class="px-3 py-2 rounded-md border border-line text-muted">Sekam</span>
               <span class="px-3 py-2 rounded-md border border-line text-muted">Menir</span>
          </div>
     </div>
@endsection