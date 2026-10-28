<div>
    @include('livewire.partials.page-header', ['title' => 'Publicar servicio'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-12 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">Publicar servicio</div>
                                        <div class="col-md-6">
                                            <a href="{{ route('sprovider.services') }}" class="btn btn-info pull-right">Mis servicios</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    @if ($limit !== null)
                                        <div class="alert alert-info">Plan gratuito: puedes publicar hasta {{ $limit }} servicios ({{ $used }} usados). <a href="{{ route('sprovider.plan') }}">Ver plan Pro</a></div>
                                    @endif
                                    @include('livewire.partials.service-form', ['submit' => 'createService', 'submitLabel' => 'Publicar servicio', 'editing' => false])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
