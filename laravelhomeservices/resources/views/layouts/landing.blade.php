<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Malakplace · Gente de oficio de confianza en Malacatos</title>
    <meta name="description" content="Gasfiteros, electricistas, jardineros y más en Malacatos, Vilcabamba, Yangana y Quinara. Mira sus reseñas y escríbeles directo por WhatsApp.">
    <meta name="theme-color" content="#06110d">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Malakplace · Gente de oficio de confianza en Malacatos">
    <meta property="og:description" content="Proveedores de Malacatos, Vilcabamba, Yangana y Quinara, a un mensaje de WhatsApp.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="https://images.unsplash.com/photo-1616547142063-6d0d6f8668ca?auto=format&fit=crop&w=1200&h=630&q=70">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@1&family=Inter+Tight:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @livewireStyles
</head>
<body class="lp min-h-screen overflow-x-hidden bg-humo-100 font-body text-grafito">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[90] focus:rounded-full focus:bg-white focus:px-4 focus:py-2">Saltar al contenido</a>

    <x-site.header />

    <main id="contenido">
        {{ $slot }}
    </main>

    <x-site.footer />

    @guest
        <x-site.auth-modal />
    @endguest

    @if (session('success') || session('status'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
             class="fixed bottom-6 left-1/2 z-[85] flex -translate-x-1/2 items-center gap-3 rounded-full bg-noche-900 px-5 py-3 text-sm font-medium text-white shadow-2xl" role="status">
            <x-site.icon name="check" class="h-5 w-5 text-oro-400" /> {{ session('success') ?? session('status') }}
        </div>
    @endif

    <x-ui.livewire-errors />

    @livewireScripts
    <script src="{{ mix('js/landing.js') }}" defer></script>
    {{-- Solo en la primera página de la sesión: otra petición en curso podría consumir el aviso de un formulario. --}}
    @unless (session()->has('visit_counted'))
    <script>
        // Contador global de visitas (una vez por sesión; lo resuelve VisitController).
        fetch('{{ route('increment.visit') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).catch(function () {});
    </script>
    @endunless
</body>
</html>
