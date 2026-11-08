{{-- Páginas de cuenta de Jetstream: usan el encabezado y el pie del sitio. --}}
<x-base-layout title="Cuenta y contraseña">
    <x-banner />
    @if (isset($header))
        <header class="mx-auto max-w-[84rem] px-5 pt-10 sm:px-8">
            {{ $header }}
        </header>
    @endif
    <div>
        {{ $slot }}
    </div>
</x-base-layout>
