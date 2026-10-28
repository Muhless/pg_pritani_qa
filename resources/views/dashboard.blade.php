@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
     <div class="mb-8">
          <h1 class="font-display text-3xl font-bold text-paddy-dark">Ringkasan Hari Ini</h1>
          <p class="mt-1 text-sm text-muted">{{ now()->translatedFormat('l, d F Y') }}</p>
     </div>

     <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          <div class="bg-paddy-deep text-white rounded-lg p-5 shadow-sm">
               <p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">Gabah Masuk Hari Ini</p>
               <p class="mt-2 font-display text-3xl font-bold">{{ number_format($gabahMasukToday ?? 0, 0, ',', '.') }} kg</p>
               <p class="mt-1 text-xs text-white/70">Total: {{ number_format($gabahMasukTotal ?? 0, 0, ',', '.') }} kg</p>
          </div>
          <div class="bg-paddy-dark text-white rounded-lg p-5 shadow-sm">
               <p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">Stok Beras</p>
               <p class="mt-2 font-display text-3xl font-bold">{{ number_format($berasStok ?? 0, 0, ',', '.') }} kg</p>
          </div>
          <div class="bg-paddy text-white rounded-lg p-5 shadow-sm">
               <p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">Penjualan Hari Ini</p>
               <p class="mt-2 font-display text-3xl font-bold">{{ $penjualanToday ?? 0 }}</p>
               <p class="mt-1 text-xs text-white/70">Total: {{ $penjualanTotal ?? 0 }}</p>
          </div>
          <div class="bg-leaf text-white rounded-lg p-5 shadow-sm">
               <p class="text-[11px] font-semibold uppercase tracking-widest text-white/70">Rendemen Rata-rata</p>
               <p class="mt-2 font-display text-3xl font-bold">{{ $avgRendemen ?? 0 }}%</p>
          </div>
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