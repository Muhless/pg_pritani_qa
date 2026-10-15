<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.ico') }}">
    <title>Masuk - Pritani</title>
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
            <h1 class="font-display text-2xl font-bold text-paddy-dark">Masuk</h1>
            <p class="mt-1 text-sm text-muted">Gunakan akun yang diberikan administrator.</p>

            @if ($errors->has('auth'))
                <div role="alert" class="mt-4 px-3 py-2 rounded-md bg-clay/10 text-clay text-sm">
                    {{ $errors->first('auth') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="login_email" class="block text-sm font-medium mb-1">Email</label>
                    <input id="login_email" name="email" type="email" value="{{ old('email') }}"
                        autocomplete="username" placeholder="nama@pritani.test"
                        class="w-full px-3 py-2 text-sm rounded-md border @error('email') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                    @error('email')
                        <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="login_password" class="block text-sm font-medium mb-1">Password</label>
                    <input id="login_password" name="password" type="password"
                        autocomplete="current-password" placeholder="Masukkan password"
                        class="w-full px-3 py-2 text-sm rounded-md border @error('password') border-clay @else border-line @enderror focus:outline-none focus:border-paddy">
                    @error('password')
                        <p role="alert" class="mt-1 text-xs text-clay">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full px-4 py-2.5 rounded-md bg-paddy text-white text-sm font-medium hover:bg-paddy-dark transition">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>