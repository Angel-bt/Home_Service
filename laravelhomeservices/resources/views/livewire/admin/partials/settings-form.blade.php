{{-- Campos de SiteSettings agrupados, para Ajustes y para Textos y aviso. --}}
<form wire:submit="save" class="space-y-6">
    @foreach ($groups as $group => [$groupTitle, $groupHelp])
        <section class="ui-card ui-card-pad">
            <h2 class="lp-title text-2xl">{{ $groupTitle }}</h2>
            <p class="mt-1 text-[14px] text-grafito-500">{{ $groupHelp }}</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach ($fields[$group] ?? [] as $key => $field)
                    @if ($field['type'] === 'checkbox')
                        <div class="sm:col-span-2">
                            <label class="flex cursor-pointer items-start gap-3 text-[15px]">
                                <input type="checkbox" wire:model="values.{{ $key }}" class="mt-0.5 h-5 w-5 shrink-0">
                                <span><span class="font-medium">{{ $field['label'] }}</span>@if ($field['hint'])<span class="block text-[13px] text-grafito-500">{{ $field['hint'] }}</span>@endif</span>
                            </label>
                            @error('values.' . $key)
                                <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <x-ui.field :label="$field['label']" :name="'values.' . $key" :for="'setting-' . $key" :hint="$field['hint'] ?: null" :class="in_array($field['type'], ['textarea', 'url'], true) ? 'sm:col-span-2' : ''">
                            @if ($field['type'] === 'textarea')
                                <textarea id="setting-{{ $key }}" rows="3" class="ui-input @error('values.' . $key) ui-input-invalid @enderror" wire:model="values.{{ $key }}"></textarea>
                            @else
                                <input id="setting-{{ $key }}" type="{{ $field['type'] }}" @if ($field['type'] === 'number') step="any" inputmode="decimal" @endif
                                       class="ui-input @error('values.' . $key) ui-input-invalid @enderror" wire:model="values.{{ $key }}">
                            @endif
                        </x-ui.field>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach

    <div class="sticky bottom-4 z-10 flex justify-end">
        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg shadow-lg" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Guardar</span>
            <span wire:loading wire:target="save">Guardando…</span>
        </button>
    </div>
</form>
