<label class="relative block">
    <span class="sr-only">Filtrar por zona</span>
    <x-site.icon name="pin" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-grafito-400" />
    <select class="ui-input pl-11" wire:model.live="zona">
        <option value="">Todas las zonas</option>
        @foreach (config('marketplace.zones') as $slug => $label)
            <option value="{{ $slug }}">{{ $label }}</option>
        @endforeach
    </select>
</label>
