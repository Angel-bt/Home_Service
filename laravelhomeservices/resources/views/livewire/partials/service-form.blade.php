{{-- Formulario de servicio compartido (admin y proveedor). <x-ui.panel> ya muestra los avisos.
     Variables: $submit (acción Livewire), $submitLabel, $categories, $editing (bool), $showFeatured (bool), $cancelUrl. --}}
@php
    $thumbField = $editing ? 'newthumbnail' : 'thumbnail';
    $imageField = $editing ? 'newimage' : 'image';
    $thumbUpload = $editing ? $newthumbnail : (is_string($thumbnail) ? null : $thumbnail);
    $imageUpload = $editing ? $newimage : (is_string($image) ? null : $image);
@endphp
<form wire:submit="{{ $submit }}" class="space-y-6">
    <section class="ui-card ui-card-pad">
        <h2 class="lp-title text-2xl">¿Qué ofreces?</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <x-ui.field label="Nombre del servicio" name="name" class="sm:col-span-2" hint="Escríbelo como lo buscaría un cliente. Ej.: «Mantenimiento de piscinas».">
                <input type="text" id="name" class="ui-input @error('name') ui-input-invalid @enderror" wire:model.blur="name" wire:change="generateSlug">
            </x-ui.field>
            <x-ui.field label="Oficio" name="service_category_id">
                <select id="service_category_id" class="ui-input @error('service_category_id') ui-input-invalid @enderror" wire:model="service_category_id">
                    <option value="">Elige un oficio</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </x-ui.field>
            <x-ui.field label="Frase corta" name="tagline" hint="Lo mejor de tu servicio en pocas palabras. Ej.: «Limpieza y químicos incluidos».">
                <input type="text" id="tagline" class="ui-input @error('tagline') ui-input-invalid @enderror" wire:model.blur="tagline">
            </x-ui.field>
            <x-ui.field label="Descripción" name="description" class="sm:col-span-2" hint="Cuenta cómo trabajas, cuánta experiencia tienes y qué debe saber el cliente.">
                <textarea id="description" rows="4" class="ui-input @error('description') ui-input-invalid @enderror" wire:model.blur="description"></textarea>
            </x-ui.field>
            <x-ui.field label="Qué incluye" name="inclusion" hint="Un punto por línea.">
                <textarea id="inclusion" rows="4" class="ui-input @error('inclusion') ui-input-invalid @enderror" wire:model.blur="inclusion" placeholder="Revisión del equipo&#10;Limpieza al terminar"></textarea>
            </x-ui.field>
            <x-ui.field label="Qué no incluye" name="exclusion" hint="Un punto por línea. Evita malentendidos con el cliente.">
                <textarea id="exclusion" rows="4" class="ui-input @error('exclusion') ui-input-invalid @enderror" wire:model.blur="exclusion" placeholder="Repuestos&#10;Materiales"></textarea>
            </x-ui.field>
        </div>
    </section>

    <section class="ui-card ui-card-pad">
        <h2 class="lp-title text-2xl">Precio</h2>
        <p class="mt-1 text-[14px] text-grafito-500">Es un precio de referencia: el final lo acuerdas con cada cliente.</p>
        <div class="mt-6 grid gap-5 sm:grid-cols-3">
            <x-ui.field label="Precio desde (USD)" name="price">
                <input type="number" step="0.01" min="0" id="price" class="ui-input @error('price') ui-input-invalid @enderror" wire:model.blur="price" placeholder="20.00" inputmode="decimal">
            </x-ui.field>
            <x-ui.field label="Descuento" name="discount" optional>
                <input type="number" step="0.01" min="0" id="discount" class="ui-input @error('discount') ui-input-invalid @enderror" wire:model.blur="discount" inputmode="decimal">
            </x-ui.field>
            <x-ui.field label="Tipo de descuento" name="discount_type" optional>
                <select id="discount_type" class="ui-input @error('discount_type') ui-input-invalid @enderror" wire:model="discount_type">
                    <option value="">Sin descuento</option>
                    <option value="fixed">En dólares ($)</option>
                    <option value="percent">En porcentaje (%)</option>
                </select>
            </x-ui.field>
        </div>
    </section>

    <section class="ui-card ui-card-pad">
        <h2 class="lp-title text-2xl">Fotos</h2>
        <p class="mt-1 text-[14px] text-grafito-500">Usa fotos reales de tu trabajo: son lo primero que mira un cliente. JPG, PNG o WebP de hasta 4 MB.</p>
        <div class="mt-6 grid gap-6 sm:grid-cols-2">
            <x-ui.field label="Foto pequeña" :name="$thumbField" for="thumb-upload" hint="Aparece en la lista de servicios.">
                <input type="file" id="thumb-upload" accept="image/*" class="ui-file" wire:model="{{ $thumbField }}">
                <p wire:loading wire:target="{{ $thumbField }}" class="ui-hint">Subiendo foto…</p>
                @if ($thumbUpload && method_exists($thumbUpload, 'temporaryUrl'))
                    <img src="{{ $thumbUpload->temporaryUrl() }}" alt="Vista previa" class="mt-3 h-28 w-40 rounded-2xl object-cover">
                @elseif ($editing && $thumbnail)
                    <img src="{{ \App\Support\Media::url('services/thumbnails', $thumbnail) }}" alt="Foto actual" class="mt-3 h-28 w-40 rounded-2xl object-cover">
                @endif
            </x-ui.field>
            <x-ui.field label="Foto grande" :name="$imageField" for="image-upload" hint="Aparece en la página del servicio.">
                <input type="file" id="image-upload" accept="image/*" class="ui-file" wire:model="{{ $imageField }}">
                <p wire:loading wire:target="{{ $imageField }}" class="ui-hint">Subiendo foto…</p>
                @if ($imageUpload && method_exists($imageUpload, 'temporaryUrl'))
                    <img src="{{ $imageUpload->temporaryUrl() }}" alt="Vista previa" class="mt-3 h-28 w-40 rounded-2xl object-cover">
                @elseif ($editing && $image)
                    <img src="{{ \App\Support\Media::url('services', $image) }}" alt="Foto actual" class="mt-3 h-28 w-40 rounded-2xl object-cover">
                @endif
            </x-ui.field>
        </div>
    </section>

    <details class="ui-card group" @error('slug') open @enderror>
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-5 text-[15px] font-medium sm:px-8 [&::-webkit-details-marker]:hidden">
            Opciones avanzadas
            <x-site.icon name="chevron-down" class="h-4 w-4 text-grafito-400 transition group-open:rotate-180" />
        </summary>
        <div class="grid gap-5 px-6 pb-6 sm:grid-cols-2 sm:px-8 sm:pb-8">
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
        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" wire:loading.attr="disabled" wire:target="{{ $submit }}, {{ $thumbField }}, {{ $imageField }}">
            <span wire:loading.remove wire:target="{{ $submit }}">{{ $submitLabel }}</span>
            <span wire:loading wire:target="{{ $submit }}">Guardando…</span>
        </button>
        @isset($cancelUrl)
            <a href="{{ $cancelUrl }}" class="ui-btn ui-btn-ghost ui-btn-lg">Cancelar</a>
        @endisset
    </div>
</form>
