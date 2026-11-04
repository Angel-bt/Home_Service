<div>
    <x-ui.panel title="Agregar" accent="servicio" help="Este servicio queda a nombre de la administración y aparece en todas las zonas. Los clientes te contactarán al número general del sitio.">
        @include('livewire.partials.service-form', ['submit' => 'createService', 'submitLabel' => 'Agregar servicio', 'editing' => false, 'cancelUrl' => route('admin.all_services')])
    </x-ui.panel>
</div>
