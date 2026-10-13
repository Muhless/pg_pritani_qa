@php
    $sections = [
        'Transaksi' => [
            ['label' => 'Pembelian Gabah', 'url' => '/purchases', 'match' => 'purchases*'],
            ['label' => 'Proses Giling',   'url' => '/milling',   'match' => 'milling*'],
            ['label' => 'Penjualan',       'url' => '/sales',     'match' => 'sales*'],
        ],
        'Gudang' => [
            ['label' => 'Stok Gudang', 'url' => '/stocks', 'match' => 'stocks*'],
        ],
        'Data Master' => [
            ['label' => 'Petani',       'url' => '/farmers',    'match' => 'farmers*'],
            ['label' => 'Pelanggan',    'url' => '/customers',  'match' => 'customers*'],
            ['label' => 'Jenis Barang', 'url' => '/item-types', 'match' => 'item-types*'],
        ],
    ];
@endphp

<aside class="w-60 shrink-0 bg-paddy-deep text-paddy-soft flex flex-col">
    <div class="px-5 py-6 border-b border-white/10">
        <p class="font-display text-xl font-bold text-white leading-none">Pritani</p>
        <p class="mt-1.5 text-[11px] uppercase tracking-widest text-paddy-soft/60">Penggilingan Padi</p>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">
        <a href="{{ url('/') }}"
            class="block px-3 py-2 rounded-md text-sm transition
            {{ request()->is('/') ? 'bg-paddy text-white font-semibold border-l-[3px] border-wheat' : 'hover:bg-white/10 hover:text-white' }}">
            Dashboard
        </a>

        @foreach ($sections as $title => $menus)
            <div>
                <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-paddy-soft/50">{{ $title }}</p>
                <div class="space-y-0.5">
                    @foreach ($menus as $menu)
                        <a href="{{ url($menu['url']) }}"
                            class="block px-3 py-2 rounded-md text-sm transition
                            {{ request()->is($menu['match']) ? 'bg-paddy text-white font-semibold border-l-[3px] border-wheat' : 'hover:bg-white/10 hover:text-white' }}">
                            {{ $menu['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="px-5 py-4 border-t border-white/10 text-xs text-paddy-soft/60">
        {{ now()->translatedFormat('d F Y') }}
    </div>
</aside>