{{-- Tarjetas de servicios. Variables: $services (colección o paginador). --}}
<div class="portfolioContainer">
    @forelse ($services as $service)
        <div class="col-xs-6 col-sm-4 col-md-3 hsgrids" style="padding-right: 5px; padding-left: 5px;" wire:key="service-card-{{ $service->id }}">
            <a class="g-list" href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}">
                <div class="img-hover">
                    <img src="{{ asset('images/services/thumbnails/' . $service->thumbnail) }}" alt="{{ $service->name }}" class="img-responsive">
                </div>
                <div class="info-gallery">
                    <h3>{{ $service->name }}</h3>
                    <hr class="separator">
                    <p>{{ $service->tagline }}</p>
                    @if ($service->reviews_avg_rating)
                        <p class="text-warning" style="margin: 0;">★ {{ number_format((float) $service->reviews_avg_rating, 1) }}</p>
                    @endif
                    <div class="content-btn"><span class="btn btn-primary">Ver y solicitar</span></div>
                    <div class="price"><span>&#36;</span><b>Desde</b>{{ $service->price }}</div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12" style="padding-bottom: 30px">
            <h5 class="text-center">{{ $emptyMessage ?? 'No hay servicios disponibles.' }}</h5>
        </div>
    @endforelse
</div>
