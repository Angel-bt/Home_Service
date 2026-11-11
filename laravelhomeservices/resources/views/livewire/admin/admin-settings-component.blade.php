<div>
    <x-ui.panel title="Ajustes" accent="del sitio" help="Lo que cambies aquí se aplica en todo el sitio apenas guardas. No hace falta tocar el código.">
        <div class="space-y-8">
            <form wire:submit="save" class="space-y-6">
                @foreach ($groups as $group => [$groupTitle, $groupHelp])
                    <section class="ui-card ui-card-pad">
                        <h2 class="lp-title text-2xl">{{ $groupTitle }}</h2>
                        <p class="mt-1 text-[14px] text-grafito-500">{{ $groupHelp }}</p>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            @foreach ($fields[$group] ?? [] as $key => $field)
                                <x-ui.field :label="$field['label']" :name="'values.' . $key" :for="'setting-' . $key" :hint="$field['hint'] ?: null">
                                    <input id="setting-{{ $key }}" type="{{ $field['type'] }}" @if ($field['type'] === 'number') step="any" inputmode="decimal" @endif
                                           class="ui-input @error('values.' . $key) ui-input-invalid @enderror" wire:model="values.{{ $key }}">
                                </x-ui.field>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                <div class="sticky bottom-4 z-10 flex justify-end">
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg shadow-lg" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Guardar ajustes</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </form>

            <section class="ui-card ui-card-pad">
                <h2 class="lp-title text-2xl">Zonas</h2>
                <p class="mt-1 text-[14px] text-grafito-500">Donde trabajan los proveedores y por donde filtran los clientes. Puedes cambiar los nombres y agregar zonas; una zona que ya usan proveedores o solicitudes no se puede quitar.</p>

                <form wire:submit="saveZones" class="mt-6 space-y-3">
                    @foreach ($zones as $slug => $label)
                        <div wire:key="zone-{{ $slug }}" class="flex flex-wrap items-center gap-3">
                            <label class="flex shrink-0 cursor-pointer items-center gap-2 text-[14px] text-grafito-600" title="Zona principal">
                                <input type="radio" wire:model="mainZone" value="{{ $slug }}" class="h-4 w-4"> Principal
                            </label>
                            <input type="text" class="ui-input min-w-0 flex-1 @error('zones.' . $slug) ui-input-invalid @enderror" wire:model="zones.{{ $slug }}" aria-label="Nombre de la zona {{ $label }}">
                            <button type="button" wire:click="removeZone('{{ $slug }}')" wire:confirm="¿Quitar la zona «{{ $label }}»?" class="ui-btn ui-btn-ghost ui-btn-sm" @disabled($slug === $mainZone)>Quitar</button>
                        </div>
                    @endforeach
                    @error('zones.*')
                        <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                    @enderror
                    <button type="submit" class="ui-btn ui-btn-secondary">Guardar zonas</button>
                </form>

                <form wire:submit="addZone" class="mt-6 flex flex-col gap-3 border-t border-humo-200 pt-6 sm:flex-row sm:items-start">
                    <x-ui.field label="Agregar una zona" name="newZone" for="new-zone" class="flex-1">
                        <input id="new-zone" type="text" class="ui-input @error('newZone') ui-input-invalid @enderror" wire:model="newZone" placeholder="Ej.: San Pedro de Vilcabamba">
                    </x-ui.field>
                    <button type="submit" class="ui-btn ui-btn-primary sm:mt-7"><x-site.icon name="plus" class="h-4 w-4" /> Agregar</button>
                </form>
            </section>
        </div>
    </x-ui.panel>
</div>
