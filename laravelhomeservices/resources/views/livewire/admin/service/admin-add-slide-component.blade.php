<div>
    <x-ui.panel title="Agregar" accent="diapositiva" help="La portada actual no muestra diapositivas; esta función viene del diseño anterior.">
        <form wire:submit="addNewSlide" class="ui-card ui-card-pad max-w-2xl space-y-5">
            <x-ui.field label="Título" name="title">
                <input type="text" id="title" class="ui-input @error('title') ui-input-invalid @enderror" wire:model.blur="title">
            </x-ui.field>
            <x-ui.field label="Imagen" name="image" for="slide-image">
                <input type="file" id="slide-image" accept="image/*" class="ui-file" wire:model="image">
                <p wire:loading wire:target="image" class="ui-hint">Subiendo imagen…</p>
                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt="Vista previa" class="mt-3 h-24 rounded-xl object-cover">
                @endif
            </x-ui.field>
            <x-ui.field label="Estado" name="status" for="status">
                <select id="status" class="ui-input @error('status') ui-input-invalid @enderror" wire:model="status">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </x-ui.field>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="ui-btn ui-btn-primary" wire:loading.attr="disabled" wire:target="addNewSlide, image">Agregar diapositiva</button>
                <a href="{{ route('admin.slider') }}" class="ui-btn ui-btn-ghost">Cancelar</a>
            </div>
        </form>
    </x-ui.panel>
</div>
