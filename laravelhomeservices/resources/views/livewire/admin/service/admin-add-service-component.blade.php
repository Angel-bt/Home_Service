<div>
    @include('livewire.partials.page-header', ['title' => 'Agregar servicio'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-12 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">Agregar servicio</div>
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.all_services') }}" class="btn btn-info pull-right">Todos los servicios</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    @include('livewire.partials.service-form', ['submit' => 'createService', 'submitLabel' => 'Agregar servicio', 'editing' => false])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
