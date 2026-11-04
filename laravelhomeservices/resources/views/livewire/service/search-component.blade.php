<div>
    <x-ui.page title="Buscar" accent="servicios" eyebrow="Malacatos y alrededores"
               lede="Escribe lo que necesitas arreglar y elige tu zona. Los resultados cambian mientras escribes.">
        <div class="grid gap-3 sm:grid-cols-[1fr_15rem]">
            <label class="relative block">
                <span class="sr-only">¿Qué necesitas?</span>
                <x-site.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-grafito-400" />
                <input type="search" class="ui-input pl-11" wire:model.live.debounce.400ms="q" placeholder="Ej.: gasfitero, jardinería, piscina" autofocus>
            </label>
            @include('livewire.partials.zone-select')
        </div>
    </x-ui.page>

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="mb-8 flex flex-wrap items-center gap-3 text-[15px] text-grafito-500">
            <p wire:loading.remove wire:target="q, zona">
                <span class="font-medium text-grafito">{{ $services->total() }}</span> {{ $services->total() === 1 ? 'servicio' : 'servicios' }}
                @if ($q !== '') para «{{ $q }}»@endif
                @if ($zona !== '') en {{ $zones[$zona] }}@endif
            </p>
            <p wire:loading wire:target="q, zona">Buscando…</p>
            @if ($q !== '' || $zona !== '')
                <button type="button" wire:click="clearFilters" class="ui-btn ui-btn-ghost ui-btn-sm"><x-site.icon name="close" class="h-4 w-4" /> Quitar filtros</button>
            @endif
        </div>

        @include('livewire.partials.service-grid', [
            'services' => $services,
            'emptyTitle' => 'No hay resultados',
            'emptyMessage' => $zona !== ''
                ? 'No encontramos ese servicio en ' . $zones[$zona] . '. Prueba en todas las zonas o con otra palabra (por ejemplo «luz» en vez de «electricista»).'
                : 'Prueba con otra palabra más corta, por ejemplo «luz» en vez de «electricista».',
            'emptyActions' => $zona !== '' ? '<button type="button" wire:click="$set(\'zona\', \'\')" class="ui-btn ui-btn-primary">Buscar en todas las zonas</button>' : null,
        ])
    </section>
</div>
