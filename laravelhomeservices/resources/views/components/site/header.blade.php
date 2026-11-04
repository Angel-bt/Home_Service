{{-- Encabezado a todo lo ancho. Transparente sobre el hero; al bajar se oculta y al subir reaparece con fondo.
     data-tone (dark|light) lo cambia landing.js según la sección que hay debajo; los colores están en app.css (.lp-header).
     Los botones de acceso abren el modal si la página lo tiene (window.lpAuth).
     solid: páginas internas (layouts/base) — tono claro y fondo desde el inicio. --}}
@props(['solid' => false])
@php
    $user = auth()->user();
    $menu = [];
    if ($user?->utype === 'ADM') {
        $menu = [
            ['Panel', route('admin.dashboard')],
            ['Servicios', route('admin.all_services')],
            ['Solicitudes', route('admin.service_requests')],
            ['Pagos Pro', route('admin.payments')],
        ];
    } elseif ($user?->utype === 'SVP') {
        $menu = [
            ['Mi panel', route('sprovider.dashboard')],
            ['Mis servicios', route('sprovider.services')],
            ['Solicitudes', route('sprovider.requests')],
            ['Mi plan', route('sprovider.plan')],
        ];
    } elseif ($user) {
        $menu = [['Mis solicitudes', route('customer.dashboard')]];
    }
    $links = [
        ['Oficios', route('home') . '#oficios'],
        ['Cómo funciona', route('home') . '#como-funciona'],
        ['Para proveedores', route('home') . '#proveedores'],
    ];
@endphp
<header data-site-header data-tone="{{ $solid ? 'light' : 'dark' }}"
        x-data="{ menu: false, account: false, scrolled: false, hidden: false, last: 0 }"
        x-init="scrolled = window.scrollY > 24"
        @scroll.window.passive="const y = window.scrollY; hidden = y > last && y > 320 && !menu && !account; last = y; scrolled = y > 24"
        @keydown.escape.window="menu = false; account = false"
        :class="{ 'is-scrolled': scrolled || menu || {{ $solid ? 'true' : 'false' }}, 'is-hidden': hidden }"
        class="lp-header fixed inset-x-0 top-0 z-[70]">
    <div class="mx-auto flex h-16 max-w-[84rem] items-center justify-between gap-6 px-5 sm:px-8 lg:h-[72px]">
        <a href="{{ route('home') }}" class="flex items-baseline" aria-label="Malakplace, inicio">
            <span class="text-[20px] font-medium tracking-[-.03em]">Malak<span class="lp-accent text-[1.15em] tracking-normal">place</span></span>
        </a>

        <nav class="hidden items-center gap-9 lg:flex" aria-label="Principal">
            @foreach ($links as [$label, $href])
                <a href="{{ $href }}" class="lp-header-link text-[13px] font-medium uppercase tracking-[.1em]">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-5">
            <a href="{{ route('search') }}" class="lp-header-link hidden sm:block" aria-label="Buscar servicios">
                <x-site.icon name="search" class="h-[18px] w-[18px]" />
            </a>
            @auth
                {{-- Acceso directo y visible al panel de cada rol: es lo primero que buscan al volver. --}}
                <a href="{{ $menu[0][1] }}" class="lp-header-cta hidden rounded-full px-5 py-2.5 text-[13px] font-medium uppercase tracking-[.1em] sm:inline-flex">
                    {{ $user->utype === 'CST' ? 'Mis solicitudes' : 'Mi panel' }}
                </a>
                <div class="relative" @click.outside="account = false">
                    <button type="button" @click="account = !account" :aria-expanded="account" class="lp-header-link flex items-center gap-2 text-[13px] font-medium uppercase tracking-[.1em]">
                        {{ explode(' ', $user->name)[0] }}
                        <x-site.icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <div x-cloak x-show="account" x-transition.origin.top.right class="absolute right-0 mt-4 w-60 rounded-2xl border border-white/10 bg-noche-900 p-2 text-white shadow-2xl">
                        @foreach ($menu as [$label, $href])
                            <a href="{{ $href }}" class="block rounded-xl px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white">{{ $label }}</a>
                        @endforeach
                        <a href="{{ route('profile.show') }}" class="block rounded-xl px-3 py-2.5 text-sm text-white/75 hover:bg-white/5 hover:text-white">Mi cuenta y seguridad</a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-white/10 pt-1">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm text-white/55 hover:bg-white/5 hover:text-white">
                                <x-site.icon name="logout" class="h-4 w-4" /> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" @click="if (window.lpAuth) { $event.preventDefault(); $dispatch('open-auth', { mode: 'login' }) }"
                   class="lp-header-link hidden text-[13px] font-medium uppercase tracking-[.1em] sm:block">Ingresar</a>
                <a href="{{ route('register') }}" @click="if (window.lpAuth) { $event.preventDefault(); $dispatch('open-auth', { mode: 'register' }) }"
                   class="lp-header-cta rounded-full px-5 py-2.5 text-[13px] font-medium uppercase tracking-[.1em]">
                    Crear cuenta
                </a>
            @endauth
            <button type="button" @click="menu = !menu" :aria-expanded="menu" class="lp-header-link -mr-1 grid h-9 w-9 place-items-center lg:hidden" aria-label="Abrir menú">
                <x-site.icon name="menu" class="h-6 w-6" x-show="!menu" />
                <x-site.icon name="close" class="h-6 w-6" x-show="menu" x-cloak />
            </button>
        </div>
    </div>

    <div x-cloak x-show="menu" x-collapse class="lg:hidden">
        <nav class="mx-auto grid max-w-[84rem] px-5 pb-6 sm:px-8" aria-label="Principal móvil">
            @auth
                <a href="{{ $menu[0][1] }}" class="lp-header-rule flex items-center justify-between border-t py-4 text-xl font-medium">{{ $user->utype === 'CST' ? 'Mis solicitudes' : 'Mi panel' }} <x-site.icon name="arrow-up-right" class="h-4 w-4 opacity-50" /></a>
            @endauth
            @foreach ($links as [$label, $href])
                <a href="{{ $href }}" @click="menu = false" class="lp-header-rule flex items-center justify-between border-t py-4 text-xl">{{ $label }} <x-site.icon name="arrow-up-right" class="h-4 w-4 opacity-50" /></a>
            @endforeach
            <a href="{{ route('search') }}" class="lp-header-rule flex items-center justify-between border-t py-4 text-xl">Buscar servicios <x-site.icon name="arrow-up-right" class="h-4 w-4 opacity-50" /></a>
            @guest
                <a href="{{ route('login') }}" @click="if (window.lpAuth) { $event.preventDefault(); menu = false; $dispatch('open-auth', { mode: 'login' }) }"
                   class="lp-header-rule flex items-center justify-between border-t py-4 text-xl">Ingresar <x-site.icon name="arrow-up-right" class="h-4 w-4 opacity-50" /></a>
            @endguest
            <span class="lp-header-rule border-t" aria-hidden="true"></span>
        </nav>
    </div>
</header>
