<div>
    <x-ui.panel title="Servicios" help="Todos los servicios publicados, tanto de los proveedores como de la administración.">
        <x-slot:actions>
            <a href="{{ route('admin.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Agregar servicio</a>
        </x-slot:actions>
        @include('livewire.admin.service.partials.service-rows', ['services' => $services])
    </x-ui.panel>
</div>
