{{-- Páginas de error: autónomas (sin base de datos, sesión ni Livewire) para que se vean aunque el sitio esté fallando.
     Secciones: code, heading, accent, text y, opcional, actions. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('heading') · Malakplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@1&family=Inter+Tight:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="lp flex min-h-screen flex-col bg-humo-100 font-body text-grafito">
    <header class="mx-auto w-full max-w-[84rem] px-5 py-6 sm:px-8">
        <a href="{{ url('/') }}" class="text-[20px] font-medium tracking-[-.03em]">Malak<span class="lp-accent text-[1.15em] tracking-normal">place</span></a>
    </header>
    <main class="mx-auto flex w-full max-w-[84rem] flex-1 flex-col justify-center px-5 pb-24 sm:px-8">
        <p class="lp-eyebrow uppercase tracking-[.14em] text-grafito-400">Error @yield('code')</p>
        <h1 class="lp-title mt-4 max-w-3xl text-5xl sm:text-7xl">@yield('heading') <span class="lp-accent">@yield('accent')</span></h1>
        <p class="mt-6 max-w-xl text-[18px] leading-relaxed text-grafito-500">@yield('text')</p>
        <div class="mt-10 flex flex-wrap gap-3">
            @section('actions')
                <a href="{{ url('/') }}" class="ui-btn ui-btn-primary ui-btn-lg">Ir al inicio</a>
            @show
            @if ($phone = config('marketplace.contact_phone'))
                <a href="{{ \App\Support\WhatsApp::link($phone, 'Hola, tuve un problema en Malakplace.') }}" class="ui-btn ui-btn-secondary ui-btn-lg">Pedir ayuda por WhatsApp</a>
            @endif
        </div>
    </main>
</body>
</html>
