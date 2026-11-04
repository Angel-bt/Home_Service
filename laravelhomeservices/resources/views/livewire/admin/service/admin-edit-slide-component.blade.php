<div>
    <x-ui.panel title="Editar" accent="diapositiva" help="La portada actual no muestra diapositivas; esta función viene del diseño anterior.">
        <form wire:submit="updateSlide" class="ui-card ui-card-pad max-w-2xl space-y-5">
            <x-ui.field label="Título" name="title">
                <input type="text" id="title" class="ui-input @error('title') ui-input-invalid @enderror" wire:model.blur="title">
            </x-ui.field>
            <x-ui.field label="Imagen" name="newimage" for="slide-image" optional>
                <input type="file" id="slide-image" accept="image/*" class="ui-file" wire:model="newimage">
                <p wire:loading wire:target="newimage" class="ui-hint">Subiendo imagen…</p>
                <img src="{{ $newimage ? $newimage->temporaryUrl() : asset('images/slider/' . $image) }}" alt="Imagen de la diapositiva" class="mt-3 h-24 rounded-xl object-cover">
            </x-ui.field>
            <x-ui.field label="Estado" name="status" for="status">
                <select id="status" class="ui-input @error('status') ui-input-invalid @enderror" wire:model="status">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </x-ui.field>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="ui-btn ui-btn-primary" wire:loading.attr="disabled" wire:target="updateSlide, newimage">Guardar cambios</button>
                <a href="{{ route('admin.slider') }}" class="ui-btn ui-btn-ghost">Volver</a>
            </div>
        </form>
    </x-ui.panel>
</div>
