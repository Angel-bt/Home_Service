<div>
    <x-ui.panel title="Nosotros" help="Este texto todavía no se edita desde el panel.">
        <x-ui.empty icon="edit" title="No hay nada que hacer aquí">
            Si quieres cambiar este texto, pídeselo a quien mantiene el sitio.
            <x-slot:actions>
                <a href="{{ route('home.about') }}" class="ui-btn ui-btn-secondary">Ver la página pública</a>
            </x-slot:actions>
        </x-ui.empty>
    </x-ui.panel>
</div>
