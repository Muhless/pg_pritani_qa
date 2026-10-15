@php
    $user = auth()->user();

    $sections = [
        'Transaksi' => [
            ['label' => 'Pembelian Gabah', 'url' => '/purchases', 'match' => 'purchases*', 'module' => 'purchases'],
            ['label' => 'Proses Giling', 'url' => '/milling', 'match' => 'milling*', 'module' => 'milling'],
            ['label' => 'Penjualan', 'url' => '/sales', 'match' => 'sales*', 'module' => 'sales'],
        ],
        'Gudang' => [
            ['label' => 'Stok Gudang', 'url' => '/stocks', 'match' => 'stocks*', 'module' => 'stocks'],
        ],
        'Data Master' => [
            ['label' => 'Petani', 'url' => '/farmers', 'match' => 'farmers*', 'module' => 'master'],
            ['label' => 'Pelanggan', 'url' => '/customers', 'match' => 'customers*', 'module' => 'master'],
            ['label' => 'Jenis Barang', 'url' => '/item-types', 'match' => 'item-types*', 'module' => 'master'],
        ],
    ];

    $visibleSections = collect($sections)
        ->map(fn($menus) => array_values(array_filter(
            $menus,
            fn($menu) => $user && $user->canAccess($menu['module'])
        )))
        ->filter()
        ->all();
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

        @foreach ($visibleSections as $title => $menus)
            <div>
                <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-widest text-paddy-soft/50">{{ $title }}
                </p>
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

    @if ($user)
        <div class="px-4 py-4 border-t border-white/10">
            <p data-testid="user-name" class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
            <p data-testid="user-role" class="text-xs text-paddy-soft/60">{{ $user->roleLabel() }}</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-3" x-data="{
                        confirmLogout(formEl) {
                            Swal.fire({
                                title: 'Keluar?',
                                text: 'Apakah kamu yakin ingin keluar?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#b4532a',
                                cancelButtonColor: '#6b7c68',
                                confirmButtonText: 'Ya, Keluar',
                                cancelButtonText: 'Batal',
                            }).then((result) => {
                                if (result.isConfirmed) formEl.submit();
                            });
                        },
                    }">
                @csrf
                <button type="button" @click="confirmLogout($el.form)"
                    class="w-full px-3 py-2 rounded-md border border-white/15 text-sm text-paddy-soft hover:bg-white/10 hover:text-white transition">
                    Keluar
                </button>
            </form>
        </div>
    @endif
</aside>