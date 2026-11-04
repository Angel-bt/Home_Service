<div>
    <x-ui.panel title="Mis servicios" help="Lo que ofreces a los clientes. Cada servicio tiene su propia página, con foto, precio y botón de WhatsApp.">
        @if ($canPublish)
            <x-slot:actions>
                <a href="{{ route('sprovider.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Publicar servicio</a>
            </x-slot:actions>
        @endif

        @unless ($canPublish)
            <div class="mb-6 flex flex-col gap-4 rounded-2xl border border-oro-400 bg-oro-300/20 p-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[15px]">Ya publicaste los {{ $limit }} servicios del plan gratuito. Para publicar más, pásate al plan Pro o elimina uno que ya no ofrezcas.</p>
                <a href="{{ route('sprovider.plan') }}" class="ui-btn ui-btn-gold ui-btn-sm shrink-0 self-start sm:self-auto">Ver plan Pro</a>
            </div>
        @endunless

        @if ($services->isEmpty())
            <x-ui.empty icon="list" title="Todavía no publicas servicios">
                Publica el primero para que los clientes te encuentren. Solo necesitas un nombre, un precio de referencia y dos fotos.
                <x-slot:actions>
                    <a href="{{ route('sprovider.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Publicar mi primer servicio</a>
                </x-slot:actions>
            </x-ui.empty>
        @else
            <ul class="space-y-3">
                @foreach ($services as $service)
                    <li wire:key="service-{{ $service->id }}" class="ui-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <img src="{{ \App\Support\Media::url('services/thumbnails', $service->thumbnail) }}" alt="" loading="lazy" class="h-20 w-full shrink-0 rounded-2xl object-cover sm:w-28">
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-[17px] font-medium">{{ $service->name }}</h2>
                            <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-[14px] text-grafito-500">
                                <span>{{ $service->category?->name }}</span>
                                <span>Desde ${{ number_format((float) $service->price, 2) }}</span>
                                <span>{{ $service->requests_count }} {{ $service->requests_count === 1 ? 'solicitud' : 'solicitudes' }}</span>
                                <span>{{ $service->reviews_avg_rating ? '★ ' . number_format((float) $service->reviews_avg_rating, 1) : 'Sin reseñas' }}</span>
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}" class="ui-btn ui-btn-ghost ui-btn-sm"><x-site.icon name="eye" class="h-4 w-4" /> Ver</a>
                            <a href="{{ route('sprovider.edit_service', ['service_slug' => $service->slug]) }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="edit" class="h-4 w-4" /> Editar</a>
                            <button type="button" wire:click="deleteService({{ $service->id }})" wire:confirm="¿Eliminar «{{ $service->name }}»? Desaparecerá de la página y no se puede deshacer." class="ui-btn ui-btn-danger ui-btn-sm"><x-site.icon name="trash" class="h-4 w-4" /> Eliminar</button>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $services->links() }}
        @endif
    </x-ui.panel>
</div>
