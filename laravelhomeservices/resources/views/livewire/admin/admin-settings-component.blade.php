<div>
    @if ($page === 'textos')
        <x-ui.panel title="Textos" accent="y aviso" help="Lo que dice la portada, el aviso destacado y cómo se ve Malakplace en Google. Se aplica apenas guardas.">
            <x-slot:actions>
                <a href="{{ route('home') }}" target="_blank" class="ui-btn ui-btn-secondary"><x-site.icon name="eye" class="h-4 w-4" /> Ver la portada</a>
            </x-slot:actions>
            @include('livewire.admin.partials.settings-form')
        </x-ui.panel>
    @else
        <x-ui.panel title="Ajustes" accent="del sitio" help="Lo que cambies aquí se aplica en todo el sitio apenas guardas. No hace falta tocar el código.">
            <div class="space-y-8">
                @include('livewire.admin.partials.settings-form')

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
    @endif
</div>
