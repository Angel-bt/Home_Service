<div>
    <x-ui.panel title="Editar" accent="servicio" help="Cambia lo que necesites y toca «Guardar cambios». Si no subes una foto nueva, se queda la actual.">
        @include('livewire.partials.service-form', ['submit' => 'updateService', 'submitLabel' => 'Guardar cambios', 'editing' => true, 'cancelUrl' => route('sprovider.services')])
    </x-ui.panel>
</div>
