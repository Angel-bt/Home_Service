<div>
    <x-ui.panel title="Agregar" accent="categoría" help="Una categoría es un oficio, por ejemplo «Gasfitería» o «Jardinería».">
        <form wire:submit="createNewCategory" class="ui-card ui-card-pad max-w-2xl space-y-5">
            <x-ui.field label="Nombre" name="name">
                <input type="text" id="name" class="ui-input @error('name') ui-input-invalid @enderror" wire:model.blur="name" wire:change="generateSlug" placeholder="Ej.: Gasfitería">
            </x-ui.field>
            <x-ui.field label="Enlace" name="slug" hint="Se crea solo a partir del nombre. Normalmente no hace falta cambiarlo.">
                <input type="text" id="slug" class="ui-input @error('slug') ui-input-invalid @enderror" wire:model.blur="slug">
            </x-ui.field>
            <x-ui.field label="Imagen" name="image" for="category-image" hint="Un ícono o foto cuadrada.">
                <x-ui.photo-input id="category-image" model="image" :max-side="800" shape="round" />
            </x-ui.field>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="ui-btn ui-btn-primary ui-submit" wire:loading.attr="disabled" wire:target="createNewCategory">Agregar categoría</button>
                <a href="{{ route('admin.service_categories') }}" class="ui-btn ui-btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.panel>
</div>
