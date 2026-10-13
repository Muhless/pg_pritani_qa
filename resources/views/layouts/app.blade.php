<!DOCTYPE html>
<html lang="id">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <title>@yield('title', 'Dashboard') - Pritani</title>
     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap"
          rel="stylesheet">
     @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-paper text-ink font-sans antialiased">
     <div class="flex min-h-screen">
          @include('partials.sidebar')

          <main class="flex-1 px-8 py-7">
               @yield('content')
          </main>
     </div>
     @stack('scripts')
</body>

</html>