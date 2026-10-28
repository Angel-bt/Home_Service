<div>
    @include('livewire.partials.page-header', ['title' => 'Mi panel de proveedor'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    @if ($profileIncomplete)
                        <div class="alert alert-warning">
                            Completa tu <a href="{{ route('sprovider.edit_profile') }}">perfil</a> (WhatsApp y zonas donde trabajas) para aparecer en las búsquedas por zona.
                        </div>
                    @endif
                    <div class="row text-center">
                        <div class="col-sm-3">
                            <div class="panel panel-default"><div class="panel-body">
                                <h2>{{ $servicesCount }}{{ $limit !== null ? ' / ' . $limit : '' }}</h2>
                                <p>Servicios publicados</p>
                            </div></div>
                        </div>
                        <div class="col-sm-3">
                            <div class="panel panel-default"><div class="panel-body">
                                <h2>{{ $pendingCount }}</h2>
                                <p>Solicitudes pendientes</p>
                            </div></div>
                        </div>
                        <div class="col-sm-3">
                            <div class="panel panel-default"><div class="panel-body">
                                <h2>{{ $completedCount }}</h2>
                                <p>Trabajos completados</p>
                            </div></div>
                        </div>
                        <div class="col-sm-3">
                            <div class="panel panel-default"><div class="panel-body">
                                <h2>{{ $rating ? number_format((float) $rating, 1) . ' ★' : '—' }}</h2>
                                <p>Calificación promedio</p>
                            </div></div>
                        </div>
                    </div>
                    <p>
                        <a href="{{ route('sprovider.services') }}" class="btn btn-primary">Mis servicios</a>
                        <a href="{{ route('sprovider.add_service') }}" class="btn btn-success">Publicar servicio</a>
                        <a href="{{ route('sprovider.requests') }}" class="btn btn-info">Solicitudes</a>
                        <a href="{{ route('sprovider.edit_profile') }}" class="btn btn-default">Editar perfil</a>
                        <a href="{{ route('sprovider.plan') }}" class="btn btn-warning">{{ $limit === null ? 'Mi plan Pro' : 'Pásate a Pro' }}</a>
                    </p>
                    <div class="panel panel-default">
                        <div class="panel-heading">Últimas solicitudes</div>
                        <div class="panel-body">
                            @forelse ($latestRequests as $request)
                                <p>
                                    {{ $request->created_at->format('d/m/Y') }} ·
                                    <b>{{ $request->name }}</b> pidió {{ $request->service?->name ?? 'un servicio' }} en {{ $request->zoneLabel() }}
                                    @include('livewire.partials.request-status', ['request' => $request])
                                </p>
                            @empty
                                <p class="text-muted">Aún no recibes solicitudes.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
