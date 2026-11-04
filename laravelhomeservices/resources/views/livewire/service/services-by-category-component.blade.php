<div>
    <x-ui.page :title="$scategory->name" eyebrow="Oficio" :back="['Todos los oficios', route('home.service_categories')]"
               lede="Elige un servicio para ver precios, reseñas y escribirle al proveedor.">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="sm:w-64">@include('livewire.partials.zone-select')</div>
            <p class="text-[15px] text-grafito-500">{{ $services->total() }} {{ $services->total() === 1 ? 'servicio' : 'servicios' }}</p>
        </div>
    </x-ui.page>

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        @include('livewire.partials.service-grid', [
            'services' => $services,
            'emptyTitle' => $zona !== '' ? 'Nadie de este oficio en esa zona todavía' : 'Todavía no hay servicios aquí',
            'emptyMessage' => $zona !== '' ? 'Prueba con «Todas las zonas»: muchos proveedores se mueven por todo el valle.' : 'Pronto habrá proveedores de este oficio. Mientras, mira los otros oficios.',
            'emptyActions' => $zona !== '' ? '<button type="button" wire:click="$set(\'zona\', \'\')" class="ui-btn ui-btn-primary">Ver todas las zonas</button>' : null,
        ])
    </section>
</div>
