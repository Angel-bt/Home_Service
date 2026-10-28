<div>
    @include('livewire.partials.page-header', ['title' => 'Editar servicio'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-12 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">Editar servicio</div>
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.all_services') }}" class="btn btn-info pull-right">Todos los servicios</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    @include('livewire.partials.service-form', ['submit' => 'updateService', 'submitLabel' => 'Guardar cambios', 'editing' => true, 'showFeatured' => true])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
