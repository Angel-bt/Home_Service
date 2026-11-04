{{-- Layout de todas las páginas internas (catálogo, paneles, cuenta, acceso) con el diseño "Valle de noche".
     No carga jQuery, Bootstrap ni Font Awesome: Tailwind (resources/css/app.css, clases ui-*) + Alpine de Livewire.
     La portada usa layouts/landing (mismo encabezado y pie, más las animaciones). --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) && $title ? $title . ' · Malakplace' : 'Malakplace · Servicios para tu hogar en Malacatos' }}</title>
    <meta name="description" content="Gasfiteros, electricistas, jardineros y más en Malacatos, Vilcabamba, Yangana y Quinara.">
    <meta name="theme-color" content="#f3f2ee">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@1&family=Inter+Tight:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @livewireStyles
</head>
<body class="lp flex min-h-screen flex-col overflow-x-hidden bg-humo-100 font-body text-grafito">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[90] focus:rounded-full focus:bg-white focus:px-4 focus:py-2">Saltar al contenido</a>

    <x-site.header solid />

    <main id="contenido" class="flex-1 pt-16 lg:pt-[72px]">
        {{ $slot }}
    </main>

    <x-site.footer />

    {{-- En las páginas de acceso el formulario ya está en la página: el modal se abriría encima con los mismos errores. --}}
    @guest
        @unless (request()->routeIs('login', 'register', 'password.*', 'two-factor.*', 'verification.*'))
            <x-site.auth-modal />
        @endunless
    @endguest

    {{-- Avisos de páginas que no son Livewire (registro, verificación de correo…). Los de Livewire van en <x-ui.flash />. --}}
    {{-- Algunos paquetes guardan en "status" claves técnicas sin espacios (verification-link-sent): esas no se muestran. --}}
    @php $toast = session('success') ?? (str_contains((string) session('status'), ' ') ? session('status') : null); @endphp
    @if ($toast && ! request()->routeIs('password.request'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
             class="fixed bottom-6 left-1/2 z-[85] flex w-[calc(100%-2rem)] max-w-md -translate-x-1/2 items-center gap-3 rounded-2xl bg-noche-900 px-5 py-4 text-[15px] text-white shadow-2xl" role="status">
            <x-site.icon name="check-circle" class="h-6 w-6 shrink-0 text-oro-400" />
            <span class="flex-1">{{ $toast }}</span>
            <button type="button" @click="show = false" class="text-white/60 hover:text-white" aria-label="Cerrar aviso"><x-site.icon name="close" class="h-5 w-5" /></button>
        </div>
    @endif

    <x-ui.livewire-errors />

    @stack('modals')
    @livewireScripts
    @stack('scripts')
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
