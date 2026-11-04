{{-- Lista de servicios del admin (todos y por categoría). El componente debe tener deleteService(). --}}
@if ($services->isEmpty())
    <x-ui.empty icon="list" title="Todavía no hay servicios">
        Agrega el primero, o espera a que los proveedores publiquen los suyos.
        <x-slot:actions>
            <a href="{{ route('admin.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Agregar servicio</a>
        </x-slot:actions>
    </x-ui.empty>
@else
    <ul class="space-y-3">
        @foreach ($services as $service)
            <li wire:key="service-{{ $service->id }}" class="ui-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                <img src="{{ asset('images/services/thumbnails/' . $service->thumbnail) }}" alt="" loading="lazy" class="h-20 w-full shrink-0 rounded-2xl object-cover sm:w-28">
                <div class="min-w-0 flex-1">
                    <h2 class="flex flex-wrap items-center gap-2 text-[17px] font-medium">
                        <span class="min-w-0 truncate">{{ $service->name }}</span>
                        @unless ($service->status)
                            <x-ui.status status="cancelled" label="Oculto" />
                        @endunless
                        @if ($service->featured)
                            <x-ui.status status="pending" label="Destacado" />
                        @endif
                    </h2>
                    <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[14px] text-grafito-500">
                        <span>{{ $service->category?->name ?? 'Sin oficio' }}</span>
                        <span>Desde ${{ number_format((float) $service->price, 2) }}</span>
                        <span>{{ $service->user?->utype === 'SVP' ? $service->user->name : 'Administración' }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}" class="ui-btn ui-btn-ghost ui-btn-sm"><x-site.icon name="eye" class="h-4 w-4" /> Ver</a>
                    <a href="{{ route('admin.edit_service', ['service_slug' => $service->slug]) }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="edit" class="h-4 w-4" /> Editar</a>
                    <button type="button" wire:click="deleteService({{ $service->id }})" wire:confirm="¿Eliminar «{{ $service->name }}»? Desaparecerá de la página y no se puede deshacer." class="ui-btn ui-btn-danger ui-btn-sm"><x-site.icon name="trash" class="h-4 w-4" /> Eliminar</button>
                </div>
            </li>
        @endforeach
    </ul>
    {{ $services->links() }}
@endif
