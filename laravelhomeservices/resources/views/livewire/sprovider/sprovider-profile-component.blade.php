<div>
    @include('livewire.partials.page-header', ['title' => 'Mi perfil'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row portfolioContainer">
                        <div class="col-md-8 col-md-offset-2 profile1">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-md-6">Perfil de proveedor</div>
                                        <div class="col-md-6">
                                            <a href="{{ route('sprovider.edit_profile') }}" class="btn btn-info pull-right">Editar perfil</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            @if ($sprovider && $sprovider->image)
                                                <img src="{{ asset('images/sproviders/' . $sprovider->image) }}" width="100%" alt=""/>
                                            @else
                                                <img src="{{ asset('images/sproviders/default.jpg') }}" width="100%" alt=""/>
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <h3>{{ Auth::user()->name }}</h3>
                                            <p>{{ $sprovider?->about ?: 'Aún no has escrito una presentación.' }}</p>
                                            <p><b>Correo:</b> {{ Auth::user()->email }}</p>
                                            <p><b>Teléfono:</b> {{ Auth::user()->phone }}</p>
                                            <p><b>WhatsApp:</b> {{ $sprovider?->whatsapp ?: 'Sin configurar' }}</p>
                                            <p><b>Ciudad / parroquia:</b> {{ $sprovider?->city ?: '—' }}</p>
                                            <p><b>Categoría principal:</b> {{ $sprovider?->category?->name ?? '—' }}</p>
                                            <p><b>Zonas donde trabaja:</b>
                                                {{ $sprovider && $sprovider->zones->isNotEmpty() ? $sprovider->zones->map->label()->implode(', ') : 'Sin zonas configuradas' }}
                                            </p>
                                            <p><b>Referencia de ubicación:</b> {{ $sprovider?->service_location ?: '—' }}</p>
                                            @if (! $sprovider || $sprovider->zones->isEmpty() || ! $sprovider->whatsapp)
                                                <div class="alert alert-warning">Completa tu WhatsApp y tus zonas para que los clientes puedan encontrarte y contactarte.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
