{{-- Tarjetas de servicios. Variables: $services (colección o paginador), $emptyTitle, $emptyMessage y, opcional, $emptyActions (HTML). --}}
@if ($services->isEmpty())
    <x-ui.empty icon="search" :title="$emptyTitle ?? 'No encontramos servicios'">
        {{ $emptyMessage ?? 'Prueba con otra palabra o mira todos los oficios.' }}
        <x-slot:actions>
            {!! $emptyActions ?? '' !!}
            <a href="{{ route('home.service_categories') }}" class="ui-btn ui-btn-secondary">Ver todos los oficios</a>
            @if ($help = \App\Support\WhatsApp::link(config('marketplace.contact_phone'), 'Hola, estoy buscando un servicio en Malakplace y no lo encuentro.'))
                <a href="{{ $help }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp"><x-site.icon name="whatsapp" class="h-5 w-5" /> Pedir ayuda</a>
            @endif
        </x-slot:actions>
    </x-ui.empty>
@else
    <div class="grid gap-x-6 gap-y-12 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach ($services as $service)
            <a href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}" class="group block" wire:key="service-card-{{ $service->id }}">
                <div class="aspect-[4/3] overflow-hidden rounded-3xl bg-humo-200">
                    <img src="{{ \App\Support\Media::url('services/thumbnails', $service->thumbnail) }}" alt="{{ $service->name }}" loading="lazy"
                         class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                </div>
                <p class="mt-4 flex items-center gap-2 text-[13px] text-grafito-500">
                    {{ $service->category?->name }}
                    @if ($service->reviews_avg_rating)
                        <span class="flex items-center gap-1 text-grafito"><x-site.icon name="star" class="h-3.5 w-3.5 text-oro-500" /> {{ number_format((float) $service->reviews_avg_rating, 1) }}</span>
                    @endif
                </p>
                <h3 class="lp-title mt-1.5 text-xl leading-snug decoration-1 underline-offset-4 group-hover:underline">{{ $service->name }}</h3>
                @if ($service->tagline)
                    <p class="mt-1 line-clamp-2 text-[14px] leading-snug text-grafito-500">{{ $service->tagline }}</p>
                @endif
                <p class="mt-3 text-[14px] text-grafito-500">
                    Desde <span class="text-[16px] font-medium text-grafito">${{ number_format($service->finalPrice(), 2) }}</span>
                    @if ($service->user?->utype === 'SVP') · {{ \Illuminate\Support\Str::before($service->user->name, ' ') ?: $service->user->name }}@endif
                </p>
            </a>
        @endforeach
    </div>
    @if (method_exists($services, 'links'))
        {{ $services->links() }}
    @endif
@endif
