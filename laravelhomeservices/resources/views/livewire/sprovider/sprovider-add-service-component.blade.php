<div>
    <x-ui.panel title="Publicar un" accent="servicio" help="Llena estos datos. Cuando toques «Publicar servicio», los clientes ya podrán verlo.">
        @if ($limit !== null && $used >= $limit)
            <x-ui.empty icon="card" title="Ya usaste tus {{ $limit }} servicios gratis">
                Para publicar otro, pásate al plan Pro o elimina uno que ya no ofrezcas.
                <x-slot:actions>
                    <a href="{{ route('sprovider.plan') }}" class="ui-btn ui-btn-gold">Ver plan Pro</a>
                    <a href="{{ route('sprovider.services') }}" class="ui-btn ui-btn-secondary">Ver mis servicios</a>
                </x-slot:actions>
            </x-ui.empty>
        @else
            @if ($limit !== null)
                <p class="mb-6 text-[14px] text-grafito-500">Plan gratuito: llevas {{ $used }} de {{ $limit }} servicios. <a href="{{ route('sprovider.plan') }}" class="ui-link">Ver plan Pro</a></p>
            @endif
            @include('livewire.partials.service-form', ['submit' => 'createService', 'submitLabel' => 'Publicar servicio', 'editing' => false, 'cancelUrl' => route('sprovider.services')])
        @endif
    </x-ui.panel>
</div>
