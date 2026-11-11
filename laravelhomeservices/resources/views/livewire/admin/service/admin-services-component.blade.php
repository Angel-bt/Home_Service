<div>
    <x-ui.panel title="Servicios" help="Todos los servicios, de los proveedores y de la administración. Ocúltalo si algo no está bien; destácalo para que salga primero en las listas.">
        <x-slot:actions>
            <a href="{{ route('admin.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Agregar servicio</a>
        </x-slot:actions>

        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <x-site.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-grafito-400" />
                <input type="search" class="ui-input pl-11" wire:model.live.debounce.400ms="q" placeholder="Buscar por nombre del servicio" aria-label="Buscar servicios">
            </div>
            <div class="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar servicios">
                @foreach (['' => 'Todos', 'hidden' => 'Ocultos', 'featured' => 'Destacados'] as $value => $label)
                    <button type="button" wire:click="$set('filter', '{{ $value }}')" @if ($filter === $value) aria-pressed="true" @endif
                            class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $filter === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                        {{ $label }} <span class="{{ $filter === $value ? 'text-white/60' : 'text-grafito-400' }}">{{ $counts[$value] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        @include('livewire.admin.service.partials.service-rows', ['services' => $services])
    </x-ui.panel>
</div>
