{{-- Tarjeta de las páginas de acceso (ingresar, registro, contraseña). Uso: <x-ui.auth-card title="Hola de" accent="nuevo" lede="…">formulario<x-slot:footer>…</x-slot:footer></x-ui.auth-card> --}}
@props(['title', 'accent' => null, 'lede' => null, 'wide' => false])
<section class="px-5 py-12 sm:px-8 lg:py-20">
    <div class="mx-auto {{ $wide ? 'max-w-xl' : 'max-w-md' }}">
        <div class="ui-card ui-card-pad">
            <h1 class="lp-title text-4xl">{{ $title }}@if ($accent) <span class="lp-accent">{{ $accent }}</span>@endif</h1>
            @if ($lede)
                <p class="mt-3 text-[15px] leading-relaxed text-grafito-500">{{ $lede }}</p>
            @endif
            <div class="mt-7">{{ $slot }}</div>
        </div>
        @isset($footer)
            <div class="mt-6 text-center text-[15px] text-grafito-500">{{ $footer }}</div>
        @endisset
    </div>
</section>
