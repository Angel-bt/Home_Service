<div>
    @include('livewire.partials.page-header', ['title' => 'Buscar servicios'])
    <section class="content-central">
        <div class="container" style="padding-top: 30px;">
            <div class="row">
                <div class="col-sm-8">
                    <input type="search" class="form-control input-lg" wire:model.live.debounce.400ms="q" placeholder="¿Qué necesitas? Ej.: plomero, jardinería, piscina" aria-label="Buscar servicios" autofocus>
                </div>
                <div class="col-sm-4">
                    <div class="input-lg" style="padding: 0;">@include('livewire.partials.zone-select')</div>
                </div>
            </div>
            <p class="text-muted" style="margin-top: 10px;">
                {{ $services->total() }} {{ $services->total() === 1 ? 'servicio encontrado' : 'servicios encontrados' }}
                @if ($q !== '') para «{{ $q }}» @endif
                @if ($zona !== '') en {{ $zones[$zona] }} @endif
            </p>
        </div>
        <div class="content_info">
            <div class="container">
                @include('livewire.partials.service-grid', ['services' => $services, 'emptyMessage' => 'No encontramos servicios con esos filtros. Prueba con otra palabra o con «Todas las zonas».'])
            </div>
            <div class="container text-center">{{ $services->links() }}</div>
        </div>
    </section>
</div>
