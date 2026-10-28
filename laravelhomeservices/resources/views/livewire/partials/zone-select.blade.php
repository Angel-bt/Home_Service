<select class="form-control" wire:model="zona" aria-label="Filtrar por zona">
    <option value="">Todas las zonas</option>
    @foreach (config('marketplace.zones') as $slug => $label)
        <option value="{{ $slug }}">{{ $label }}</option>
    @endforeach
</select>
