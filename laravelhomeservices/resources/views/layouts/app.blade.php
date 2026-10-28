{{-- Páginas de cuenta de Jetstream (perfil, tokens): usan el encabezado y menús del sitio. --}}
<x-base-layout>
    <div class="bg-gray-100" style="min-height: 60vh;">
        <x-banner />

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main>
            {{ $slot }}
        </main>
    </div>
</x-base-layout>
