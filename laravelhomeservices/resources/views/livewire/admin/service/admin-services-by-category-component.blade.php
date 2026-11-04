<div>
    <x-ui.panel :title="$category_name" help="Los servicios publicados en este oficio.">
        <x-slot:actions>
            <a href="{{ route('admin.service_categories') }}" class="ui-btn ui-btn-secondary"><x-site.icon name="arrow-left" class="h-4 w-4" /> Categorías</a>
            <a href="{{ route('admin.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Agregar servicio</a>
        </x-slot:actions>
        @include('livewire.admin.service.partials.service-rows', ['services' => $services])
    </x-ui.panel>
</div>
