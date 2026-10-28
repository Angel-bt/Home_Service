<div>
    @include('livewire.partials.page-header', ['title' => $scategory->name])
    <section class="content-central">
        <div class="container">
            <div class="row" style="margin-top: -30px;">
                <div class="titles">
                    <h2>Servicios de <span>{{ $scategory->name }}</span></h2>
                    <hr class="tall">
                </div>
            </div>
            <div class="row" style="margin-bottom: 20px;">
                <div class="col-sm-4 col-sm-offset-8">
                    @include('livewire.partials.zone-select')
                </div>
            </div>
        </div>
        <div class="content_info">
            <div class="container">
                @include('livewire.partials.service-grid', ['services' => $services, 'emptyMessage' => $zona !== '' ? 'No hay servicios de esta categoría en la zona elegida todavía.' : 'Todavía no hay servicios en esta categoría.'])
            </div>
            <div class="container text-center">{{ $services->links() }}</div>
        </div>
    </section>
</div>
