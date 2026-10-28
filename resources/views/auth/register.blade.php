<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
    <title>Daftar - Pritani</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paddy-deep font-sans antialiased min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <p class="font-display text-4xl font-bold text-white">Pritani</p>
            <p class="mt-2 text-xs uppercase tracking-widest text-paddy-soft/70">Penggilingan Padi</p>
        </div>

        <div class="bg-card rounded-lg shadow-xl p-7">
            <h1 class="font-display text-2xl font-bold text-paddy-dark">Daftar</h1>
            <p class="mt-1 text-sm text-muted">Buat akun baru.</p>

            <form method="POST" action="{{ route('register') }}" novalidate class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}"
                        autocomplete="name" placeholder="Nama lengkap"
                        class="w-full px-3 py-2 text-sm rounded-md border @error('name') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                    @error('name')
                        <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                        autocomplete="username" placeholder="nama@pritani.test"
                        class="w-full px-3 py-2 text-sm rounded-md border @error('email') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                    @error('email')
                        <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password"
                            autocomplete="new-password" placeholder="Masukkan password"
                            class="w-full px-3 py-2 pr-10 text-sm rounded-md border @error('password') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 flex items-center px-3 text-muted hover:text-paddy-dark transition" aria-label="Tampilkan password">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                        autocomplete="new-password" placeholder="Ulangi password"
                        class="w-full px-3 py-2 text-sm rounded-md border border-line focus:outline-none focus:border-paddy">
                </div>

                <button type="submit"
                    class="w-full px-4 py-2.5 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
                    Daftar
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-muted">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-paddy hover:text-paddy-dark font-medium">Masuk</a>
            </p>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            eyeIcon.classList.toggle('hidden');
            eyeSlashIcon.classList.toggle('hidden');
            
            this.setAttribute('aria-label', type === 'password' ? 'Tampilkan password' : 'Sembunyikan password');
        });
    </script>
</body>
</html>
