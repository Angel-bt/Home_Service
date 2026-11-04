{{-- Formulario de servicio compartido (admin y proveedor). Lo esencial a la vista; lo demás, en «Más detalles». <x-ui.panel> ya muestra los avisos.
     Variables: $submit (acción Livewire), $submitLabel, $categories, $editing (bool), $showFeatured (bool), $cancelUrl. --}}
@php
    $thumbField = $editing ? 'newthumbnail' : 'thumbnail';
    $imageField = $editing ? 'newimage' : 'image';
    $detailErrors = $errors->hasAny(['tagline', 'inclusion', 'exclusion', 'discount', 'discount_type', 'slug']);
@endphp
<form wire:submit="{{ $submit }}" class="space-y-6">
    <section class="ui-card ui-card-pad">
        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.field label="¿Qué servicio ofreces?" name="name" class="sm:col-span-2" hint="Escríbelo como lo buscaría un cliente. Ej.: «Mantenimiento de piscinas».">
                <input type="text" id="name" class="ui-input @error('name') ui-input-invalid @enderror" wire:model.blur="name" wire:change="generateSlug" autocomplete="off">
            </x-ui.field>
            <x-ui.field label="Oficio" name="service_category_id">
                <select id="service_category_id" class="ui-input @error('service_category_id') ui-input-invalid @enderror" wire:model="service_category_id">
                    <option value="">Elige un oficio</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Precio desde (dólares)" name="price" hint="De referencia: el final lo acuerdas con cada cliente.">
                <input type="number" step="0.01" min="0" id="price" class="ui-input @error('price') ui-input-invalid @enderror" wire:model.blur="price" placeholder="20" inputmode="decimal">
            </x-ui.field>
            <x-ui.field label="Foto del servicio" :name="$imageField" for="service-photo" class="sm:col-span-2" hint="Una foto real de tu trabajo: es lo primero que mira un cliente. Puedes tomarla con el celular.">
                <x-ui.photo-input id="service-photo" :model="$imageField" :thumb="$thumbField" :current="$editing && $image ? \App\Support\Media::url('services', $image) : null" empty="Toca para tomar o elegir una foto" class="max-w-xl" />
            </x-ui.field>
            <x-ui.field label="Cuéntale al cliente cómo trabajas" name="description" class="sm:col-span-2" hint="Con dos o tres líneas basta: tu experiencia y qué debe saber.">
                <textarea id="description" rows="3" class="ui-input @error('description') ui-input-invalid @enderror" wire:model.blur="description" placeholder="Tengo 10 años de experiencia. Voy a domicilio y dejo todo limpio."></textarea>
            </x-ui.field>
        </div>
    </section>

    <details class="ui-card group" @if ($detailErrors) open @endif>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 sm:px-8 [&::-webkit-details-marker]:hidden">
            <span>
                <span class="block text-[15px] font-medium">Más detalles</span>
                <span class="block text-[13px] text-grafito-500">Opcional: qué incluye, descuento y otros.</span>
            </span>
            <x-site.icon name="chevron-down" class="h-4 w-4 shrink-0 text-grafito-400 transition group-open:rotate-180" />
        </summary>
        <div class="grid gap-5 px-6 pb-6 sm:grid-cols-2 sm:px-8 sm:pb-8">
            <x-ui.field label="Frase corta" name="tagline" class="sm:col-span-2" hint="Lo mejor de tu servicio. Ej.: «Limpieza y químicos incluidos».">
                <input type="text" id="tagline" class="ui-input @error('tagline') ui-input-invalid @enderror" wire:model.blur="tagline">
            </x-ui.field>
            <x-ui.field label="Qué incluye" name="inclusion" hint="Un punto por línea.">
                <textarea id="inclusion" rows="3" class="ui-input @error('inclusion') ui-input-invalid @enderror" wire:model.blur="inclusion" placeholder="Revisión del equipo&#10;Limpieza al terminar"></textarea>
            </x-ui.field>
            <x-ui.field label="Qué no incluye" name="exclusion" hint="Un punto por línea. Evita malentendidos.">
                <textarea id="exclusion" rows="3" class="ui-input @error('exclusion') ui-input-invalid @enderror" wire:model.blur="exclusion" placeholder="Repuestos&#10;Materiales"></textarea>
            </x-ui.field>
            <x-ui.field label="Descuento" name="discount">
                <input type="number" step="0.01" min="0" id="discount" class="ui-input @error('discount') ui-input-invalid @enderror" wire:model.blur="discount" inputmode="decimal">
            </x-ui.field>
            <x-ui.field label="Tipo de descuento" name="discount_type">
                <select id="discount_type" class="ui-input @error('discount_type') ui-input-invalid @enderror" wire:model="discount_type">
                    <option value="">Sin descuento</option>
                    <option value="fixed">En dólares ($)</option>
                    <option value="percent">En porcentaje (%)</option>
                </select>
            </x-ui.field>
            <x-ui.field label="Enlace del servicio" name="slug" hint="Se crea solo a partir del nombre. Normalmente no hace falta cambiarlo.">
                <input type="text" id="slug" class="ui-input @error('slug') ui-input-invalid @enderror" wire:model.blur="slug">
            </x-ui.field>
            @if ($showFeatured ?? false)
                <x-ui.field label="¿Destacar en la portada?" name="featured">
                    <select id="featured" class="ui-input" wire:model="featured">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </x-ui.field>
            @endif
        </div>
    </details>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-submit" wire:loading.attr="disabled" wire:target="{{ $submit }}">
            <span wire:loading.remove wire:target="{{ $submit }}">{{ $submitLabel }}</span>
            <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
        </button>
        @isset($cancelUrl)
            <a href="{{ $cancelUrl }}" class="ui-btn ui-btn-ghost ui-btn-lg">Cancelar</a>
        @endisset
    </div>
</form>
