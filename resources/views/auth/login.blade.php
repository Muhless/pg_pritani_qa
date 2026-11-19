<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>Masuk - Pritani</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paddy-deep font-sans antialiased">
    <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-paddy/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-16 h-80 w-80 rounded-full bg-wheat/10 blur-3xl"></div>
    </div>

    <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-7 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-paddy text-white shadow-lg shadow-paddy/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-7 w-7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5c-8 0-14 3.8-14 10a5.5 5.5 0 0 0 5.5 5.5c6.2 0 10-6 8.5-15.5Z" />
                        <path stroke-linecap="round" d="M5 20c3.5-4.5 7-7 11-9" />
                    </svg>
                </div>
                <p class="font-display text-4xl font-bold text-white">Pritani</p>
                <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-paddy-soft/70">Penggilingan Padi</p>
            </div>

            <div class="rounded-2xl border border-white/70 bg-card p-6 shadow-2xl shadow-black/25 ring-1 ring-black/5 sm:p-8">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-paddy">Selamat datang</p>
                <h1 class="mt-2 font-display text-2xl font-bold text-paddy-dark">Masuk</h1>
                <p class="mt-1.5 text-sm text-muted">Gunakan akun yang diberikan administrator.</p>

                @if ($errors->has('auth'))
                    <div role="alert" class="mt-5 flex items-start gap-2 rounded-lg border border-clay/20 bg-clay/10 px-3 py-2.5 text-sm text-clay">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.4 17.5a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                        </svg>
                        <span>{{ $errors->first('auth') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="login_email" class="mb-1.5 block text-sm font-medium text-paddy-dark">Email</label>
                        <input id="login_email" name="email" type="email" value="{{ old('email') }}"
                            autocomplete="username" placeholder="nama@pritani.test"
                            class="w-full rounded-lg border @error('email') border-clay @else border-line @enderror bg-white px-3.5 py-2.5 text-sm text-ink transition placeholder:text-muted/70 focus:border-paddy focus:outline-none focus:ring-4 focus:ring-paddy/15">
                        @error('email')
                            <p role="alert" class="mt-1.5 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="login_password" class="mb-1.5 block text-sm font-medium text-paddy-dark">Password</label>
                        <div class="relative">
                            <input id="login_password" name="password" type="password"
                                autocomplete="current-password" placeholder="Masukkan password"
                                class="w-full rounded-lg border @error('password') border-clay @else border-line @enderror bg-white py-2.5 pl-3.5 pr-11 text-sm text-ink transition placeholder:text-muted/70 focus:border-paddy focus:outline-none focus:ring-4 focus:ring-paddy/15">
                            <button type="button" id="togglePassword" aria-label="Tampilkan password"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-muted transition hover:text-paddy-dark focus-visible:outline-none focus-visible:text-paddy-dark">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="hidden h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p role="alert" class="mt-1.5 text-xs text-clay">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-line text-paddy accent-paddy focus:ring-paddy">
                        <label for="remember" class="text-sm text-muted">Ingat saya</label>
                    </div>

                    <button type="submit"
                        class="group flex w-full items-center justify-center gap-2 rounded-lg bg-paddy px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-paddy-dark focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-paddy/25 active:translate-y-px">
                        Masuk
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>

                <p class="mt-6 border-t border-line pt-5 text-center text-sm text-muted">
                    Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-paddy transition hover:text-paddy-dark">Daftar</a>
                </p>
            </div>
        </div>
    </main>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('login_password');
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
