<div>
    <x-ui.panel title="Términos de uso" help="Este texto todavía no se edita desde el panel.">
        <x-ui.empty icon="edit" title="No hay nada que hacer aquí">
            Si quieres cambiar este texto, pídeselo a quien mantiene el sitio.
            <x-slot:actions>
                <a href="{{ route('home.terms') }}" class="ui-btn ui-btn-secondary">Ver la página pública</a>
            </x-slot:actions>
        </x-ui.empty>
    </x-ui.panel>
</div>
