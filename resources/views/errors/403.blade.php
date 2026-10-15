@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="max-w-lg mx-auto mt-16 text-center">
        <p class="font-display text-7xl font-bold text-paddy">403</p>
        <h1 class="mt-2 font-display text-2xl font-bold text-paddy-dark">Akses ditolak</h1>
        <p class="mt-2 text-sm text-muted">Peran Anda tidak memiliki izin untuk membuka halaman ini.</p>
        <a href="{{ route('dashboard') }}"
            class="inline-block mt-6 px-4 py-2 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
            Kembali ke Dashboard
        </a>
    </div>
@endsection