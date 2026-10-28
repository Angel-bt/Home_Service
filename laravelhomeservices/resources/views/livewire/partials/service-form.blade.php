{{-- Campos compartidos del formulario de servicio (admin y proveedor).
     Variables: $submit (acción Livewire), $submitLabel, $categories, $editing (bool), $showFeatured (bool). --}}
@if (Session::has('message'))
    <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
@endif
<form class="form-horizontal" wire:submit.prevent="{{ $submit }}">
    <div class="form-group">
        <label for="name" class="control-label col-sm-3">Nombre:</label>
        <div class="col-sm-9">
            <input type="text" id="name" class="form-control" wire:model.lazy="name" wire:change="generateSlug" placeholder="Ej.: Mantenimiento de piscinas"/>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="slug" class="control-label col-sm-3">Enlace (slug):</label>
        <div class="col-sm-9">
            <input type="text" id="slug" class="form-control" wire:model.lazy="slug"/>
            @error('slug') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="tagline" class="control-label col-sm-3">Frase corta:</label>
        <div class="col-sm-9">
            <input type="text" id="tagline" class="form-control" wire:model.lazy="tagline" placeholder="Ej.: Limpieza y químicos incluidos"/>
            @error('tagline') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="service_category_id" class="control-label col-sm-3">Categoría:</label>
        <div class="col-sm-9">
            <select id="service_category_id" class="form-control" wire:model="service_category_id">
                <option value="">Selecciona una categoría</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('service_category_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="price" class="control-label col-sm-3">Precio desde (USD):</label>
        <div class="col-sm-9">
            <input type="number" step="0.01" min="0" id="price" class="form-control" wire:model.lazy="price"/>
            @error('price') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="discount" class="control-label col-sm-3">Descuento:</label>
        <div class="col-sm-5">
            <input type="number" step="0.01" min="0" id="discount" class="form-control" wire:model.lazy="discount"/>
            @error('discount') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="col-sm-4">
            <select id="discount_type" class="form-control" wire:model="discount_type">
                <option value="">Tipo de descuento</option>
                <option value="fixed">Valor fijo ($)</option>
                <option value="percent">Porcentaje (%)</option>
            </select>
            @error('discount_type') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    @if ($showFeatured ?? false)
        <div class="form-group">
            <label for="featured" class="control-label col-sm-3">Destacado:</label>
            <div class="col-sm-9">
                <select id="featured" class="form-control" wire:model="featured">
                    <option value="0">No</option>
                    <option value="1">Sí</option>
                </select>
            </div>
        </div>
    @endif
    <div class="form-group">
        <label for="description" class="control-label col-sm-3">Descripción:</label>
        <div class="col-sm-9">
            <textarea id="description" rows="4" class="form-control" wire:model.lazy="description"></textarea>
            @error('description') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="inclusion" class="control-label col-sm-3">Qué incluye:</label>
        <div class="col-sm-9">
            <textarea id="inclusion" rows="3" class="form-control" wire:model.lazy="inclusion" placeholder="Una línea por cada punto"></textarea>
            @error('inclusion') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label for="exclusion" class="control-label col-sm-3">Qué no incluye:</label>
        <div class="col-sm-9">
            <textarea id="exclusion" rows="3" class="form-control" wire:model.lazy="exclusion" placeholder="Una línea por cada punto"></textarea>
            @error('exclusion') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-3">Miniatura:</label>
        <div class="col-sm-9">
            @if ($editing)
                <input type="file" accept="image/*" class="form-control-file" wire:model="newthumbnail"/>
                @error('newthumbnail') <div class="text-danger">{{ $message }}</div> @enderror
                @if ($newthumbnail)
                    <img src="{{ $newthumbnail->temporaryUrl() }}" width="80" alt=""/>
                @elseif ($thumbnail)
                    <img src="{{ asset('images/services/thumbnails/' . $thumbnail) }}" width="80" alt=""/>
                @endif
            @else
                <input type="file" accept="image/*" class="form-control-file" wire:model="thumbnail"/>
                @error('thumbnail') <div class="text-danger">{{ $message }}</div> @enderror
                @if ($thumbnail && ! is_string($thumbnail))
                    <img src="{{ $thumbnail->temporaryUrl() }}" width="80" alt=""/>
                @endif
            @endif
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-3">Imagen principal:</label>
        <div class="col-sm-9">
            @if ($editing)
                <input type="file" accept="image/*" class="form-control-file" wire:model="newimage"/>
                @error('newimage') <div class="text-danger">{{ $message }}</div> @enderror
                @if ($newimage)
                    <img src="{{ $newimage->temporaryUrl() }}" width="80" alt=""/>
                @elseif ($image)
                    <img src="{{ asset('images/services/' . $image) }}" width="80" alt=""/>
                @endif
            @else
                <input type="file" accept="image/*" class="form-control-file" wire:model="image"/>
                @error('image') <div class="text-danger">{{ $message }}</div> @enderror
                @if ($image && ! is_string($image))
                    <img src="{{ $image->temporaryUrl() }}" width="80" alt=""/>
                @endif
            @endif
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-9">
            <button type="submit" class="btn btn-success pull-right" wire:loading.attr="disabled">{{ $submitLabel }}</button>
        </div>
    </div>
</form>
