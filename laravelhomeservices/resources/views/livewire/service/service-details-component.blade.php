<div>
    @if ($service)
        @include('livewire.partials.page-header', ['title' => $service->name])

    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 single-blog">
                            <div class="post-item">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="post-header">
                                            <div class="post-format-icon post-format-standard" style="margin-top: -10px;">
                                                <i class="fa fa-image"></i>
                                            </div>
                                            <div class="post-info-wrap">
                                                <h2 class="post-title">{{ $service->name }}: {{ $service->category?->name }}</h2>
                                                <div class="post-meta">
                                                    @if ($service->reviews_count > 0)
                                                        <span class="text-warning">{{ str_repeat('★', (int) round($service->reviews_avg_rating)) }}{{ str_repeat('☆', 5 - (int) round($service->reviews_avg_rating)) }}</span>
                                                        {{ number_format((float) $service->reviews_avg_rating, 1) }} ({{ $service->reviews_count }} {{ $service->reviews_count === 1 ? 'reseña' : 'reseñas' }})
                                                    @else
                                                        Sin reseñas todavía
                                                    @endif
                                                    · {{ number_format($views) }} visitas
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div id="single-carousel">
                                            <div class="img-hover">
                                                <img src="{{ asset('images/services/' . $service->image) }}" alt="{{ $service->name }}" class="img-responsive">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="post-content">
                                            <h3>{{ $service->name }}</h3>
                                            <p>{{ $service->description }}</p>
                                            <h4>Qué incluye</h4>
                                            <ul class="list-styles">
                                                @foreach (array_filter(explode('|', (string) $service->inclusion)) as $inclusion)
                                                    <li><i class="fa fa-check"></i>{{ $inclusion }}</li>
                                                @endforeach
                                            </ul>
                                            <h4>Qué no incluye</h4>
                                            <ul class="list-styles">
                                                @foreach (array_filter(explode('|', (string) $service->exclusion)) as $exclusion)
                                                    <li><i class="fa fa-minus"></i>{{ $exclusion }}</li>
                                                @endforeach
                                            </ul>

                                            <h4 id="resenas" style="margin-top: 30px;">Reseñas de clientes</h4>
                                            @forelse ($reviews as $review)
                                                <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
                                                    <strong>{{ $review->user?->name ?? 'Cliente' }}</strong>
                                                    <span class="text-warning">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                                    <small class="text-muted">· {{ $review->created_at->format('d/m/Y') }}</small>
                                                    @if ($review->comment)
                                                        <p style="margin: 5px 0 0;">{{ $review->comment }}</p>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-muted">Aún no hay reseñas. Solo los clientes que contrataron el servicio pueden calificarlo.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <aside class="widget" style="margin-top: 18px;">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Precio</div>
                                    <div class="panel-body">
                                        <table class="table">
                                            <tr>
                                                <td style="border-top: none;">Precio desde</td>
                                                <td style="border-top: none;">${{ number_format((float) $service->price, 2) }}</td>
                                            </tr>
                                            @if ($service->discount)
                                                <tr>
                                                    <td>Descuento</td>
                                                    <td>{{ $service->discount_type === 'percent' ? rtrim(rtrim(number_format((float) $service->discount, 2), '0'), '.') . '%' : '$' . number_format((float) $service->discount, 2) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td><b>Total referencial</b></td>
                                                <td><b>${{ number_format($service->finalPrice(), 2) }}</b></td>
                                            </tr>
                                        </table>
                                        <small class="text-muted">El precio final lo acuerdas con el proveedor según el trabajo.</small>
                                    </div>
                                    <div class="panel-footer">
                                        @if ($whatsapp = $service->whatsappLink())
                                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn btn-success btn-block" style="background:#25D366;border-color:#25D366;">
                                                <i class="fab fa-whatsapp"></i> Escribir por WhatsApp
                                            </a>
                                        @endif
                                        <a href="#solicitar" class="btn btn-primary btn-block">Solicitar servicio</a>
                                    </div>
                                </div>
                            </aside>

                            @if ($service->user && $service->user->utype === 'SVP')
                                @php $provider = $service->user->serviceProvider; @endphp
                                <aside class="widget">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Proveedor</div>
                                        <div class="panel-body">
                                            <p><b>{{ $service->user->name }}</b></p>
                                            @if ($provider?->about)
                                                <p>{{ $provider->about }}</p>
                                            @endif
                                            @if ($provider && $provider->zones->isNotEmpty())
                                                <p><i class="fa fa-map-marker"></i> Atiende en: {{ $provider->zones->map->label()->implode(', ') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </aside>
                            @endif

                            <aside class="widget" id="solicitar">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Solicitar este servicio</div>
                                    <div class="panel-body">
                                        @if (Session::has('request_sent'))
                                            <div class="alert alert-success">{{ Session::get('request_sent') }}</div>
                                        @endif
                                        @guest
                                            <p>Inicia sesión o crea una cuenta gratis para enviar tu solicitud y luego dejar tu reseña.</p>
                                            <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
                                            <a href="{{ route('register') }}" class="btn btn-default">Crear cuenta</a>
                                        @else
                                            <form wire:submit.prevent="submitRequest">
                                                <div class="form-group">
                                                    <label for="req-name">Nombre</label>
                                                    <input type="text" id="req-name" class="form-control" wire:model="name">
                                                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="req-phone">Celular / WhatsApp</label>
                                                    <input type="tel" id="req-phone" class="form-control" wire:model="phone" placeholder="0991234567">
                                                    @error('phone') <div class="text-danger">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="req-zone">Zona</label>
                                                    <select id="req-zone" class="form-control" wire:model="zone">
                                                        <option value="">Selecciona tu zona</option>
                                                        @foreach ($zones as $slug => $label)
                                                            <option value="{{ $slug }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('zone') <div class="text-danger">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="req-address">Dirección o referencia</label>
                                                    <input type="text" id="req-address" class="form-control" wire:model="address" placeholder="Barrio, quinta, referencia">
                                                    @error('address') <div class="text-danger">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="req-date">Fecha preferida (opcional)</label>
                                                    <input type="date" id="req-date" class="form-control" wire:model="preferred_date" min="{{ now()->toDateString() }}">
                                                    @error('preferred_date') <div class="text-danger">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="req-message">¿Qué necesitas?</label>
                                                    <textarea id="req-message" rows="3" class="form-control" wire:model="message"></textarea>
                                                    @error('message') <div class="text-danger">{{ $message }}</div> @enderror
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled">Enviar solicitud</button>
                                            </form>
                                        @endguest
                                    </div>
                                </div>
                            </aside>

                            @if ($r_service)
                                <aside>
                                    <h3>Servicio relacionado</h3>
                                    <div class="col-md-12 col-sm-6 col-xs-12 bg-dark color-white padding-top-mini" style="max-width: 360px">
                                        <a href="{{ route('home.service_details', ['service_slug' => $r_service->slug]) }}">
                                            <div class="img-hover">
                                                <img src="{{ asset('images/services/thumbnails/' . $r_service->thumbnail) }}" alt="{{ $r_service->name }}" class="img-responsive">
                                            </div>
                                            <div class="info-gallery">
                                                <h3>{{ $r_service->name }}</h3>
                                                <hr class="separator">
                                                <p>{{ $r_service->tagline }}</p>
                                                <div class="content-btn"><span class="btn btn-warning">Ver detalles</span></div>
                                                <div class="price"><span>&#36;</span><b>Desde</b>{{ $r_service->price }}</div>
                                            </div>
                                        </a>
                                    </div>
                                </aside>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @else
        <div class="container" style="padding: 40px 15px;">
            <div class="alert alert-danger">
                El servicio solicitado no está disponible. Revisa el enlace o <a href="{{ route('search') }}">busca otro servicio</a>.
            </div>
        </div>
    @endif
</div>
