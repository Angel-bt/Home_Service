<div>
    <x-ui.panel title="Editar" accent="categoría" help="Cambia lo que necesites y toca «Guardar cambios». Si no subes una imagen nueva, se queda la actual.">
        <form wire:submit="updateServiceCategory" class="ui-card ui-card-pad max-w-2xl space-y-5">
            <x-ui.field label="Nombre" name="name">
                <input type="text" id="name" class="ui-input @error('name') ui-input-invalid @enderror" wire:model.blur="name" wire:change="generateSlug">
            </x-ui.field>
            <x-ui.field label="Enlace" name="slug" hint="Si lo cambias, los enlaces antiguos a esta categoría dejarán de funcionar.">
                <input type="text" id="slug" class="ui-input @error('slug') ui-input-invalid @enderror" wire:model.blur="slug">
            </x-ui.field>
            <x-ui.field label="Imagen" name="newimage" for="category-image" optional>
                <input type="file" id="category-image" accept="image/*" class="ui-file" wire:model="newimage">
                <p wire:loading wire:target="newimage" class="ui-hint">Subiendo imagen…</p>
                <img src="{{ $newimage ? $newimage->temporaryUrl() : asset('images/categories/' . $image) }}" alt="Imagen de la categoría" class="mt-3 h-20 w-20 rounded-xl object-contain">
            </x-ui.field>
            <x-ui.field label="¿Destacarla?" name="featured" for="featured">
                <select id="featured" class="ui-input" wire:model="featured">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </x-ui.field>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="ui-btn ui-btn-primary" wire:loading.attr="disabled" wire:target="updateServiceCategory, newimage">Guardar cambios</button>
                <a href="{{ route('admin.service_categories') }}" class="ui-btn ui-btn-ghost">Volver</a>
            </div>
        </form>
    </x-ui.panel>
</div>
