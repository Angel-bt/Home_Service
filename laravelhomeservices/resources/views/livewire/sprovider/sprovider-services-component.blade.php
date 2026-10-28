<div>
    @include('livewire.partials.page-header', ['title' => 'Mis servicios'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-12 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">Mis servicios publicados</div>
                                        <div class="col-md-6">
                                            @if ($canPublish)
                                                <a href="{{ route('sprovider.add_service') }}" class="btn btn-success pull-right">Publicar servicio</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    @if (Session::has('message'))
                                        <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
                                    @endif
                                    @if (! $canPublish)
                                        <div class="alert alert-warning">
                                            Llegaste al límite de {{ $limit }} servicios del plan gratuito. <a href="{{ route('sprovider.plan') }}">Pásate al plan Pro</a> para publicar sin límite.
                                        </div>
                                    @endif
                                    @if ($services->isEmpty())
                                        <p class="text-center">Todavía no publicas servicios. ¡Publica el primero para que los clientes te encuentren!</p>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Imagen</th>
                                                        <th>Servicio</th>
                                                        <th>Categoría</th>
                                                        <th>Precio</th>
                                                        <th>Solicitudes</th>
                                                        <th>Calificación</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($services as $service)
                                                        <tr>
                                                            <td><img src="{{ asset('images/services/thumbnails/' . $service->thumbnail) }}" width="60" alt=""></td>
                                                            <td><a href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}">{{ $service->name }}</a></td>
                                                            <td>{{ $service->category?->name }}</td>
                                                            <td>${{ number_format((float) $service->price, 2) }}</td>
                                                            <td>{{ $service->requests_count }}</td>
                                                            <td>{{ $service->reviews_avg_rating ? number_format((float) $service->reviews_avg_rating, 1) . ' ★' : '—' }}</td>
                                                            <td>
                                                                <a href="{{ route('sprovider.edit_service', ['service_slug' => $service->slug]) }}" title="Editar"><i class="fa fa-edit fa-2x text-info"></i></a>
                                                                <a href="#" title="Eliminar" onclick="confirm('¿Eliminar este servicio?') || event.stopImmediatePropagation()" wire:click.prevent="deleteService({{ $service->id }})" style="margin-left: 10px;"><i class="fa fa-times fa-2x text-danger"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        {{ $services->links() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
