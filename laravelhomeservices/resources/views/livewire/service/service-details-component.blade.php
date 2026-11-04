<div>
    @if ($service)
        @php
            $isProvider = $service->user?->utype === 'SVP';
            $provider = $isProvider ? $service->user->serviceProvider : null;
            $whatsapp = $service->whatsappLink();
            $rating = $service->reviews_count > 0 ? (float) $service->reviews_avg_rating : null;
            $authUser = auth()->user();
            $isOwnService = $authUser && $authUser->id === $service->user_id;
        @endphp

        <section class="mx-auto max-w-[84rem] px-5 pt-8 sm:px-8 lg:pt-12">
            @if ($service->category)
                <a href="{{ route('home.services_by_category', ['category_slug' => $service->category->slug]) }}" class="inline-flex items-center gap-2 text-[14px] text-grafito-500 hover:text-grafito">
                    <x-site.icon name="arrow-left" class="h-4 w-4" /> Más de {{ $service->category->name }}
                </a>
            @endif
            <h1 class="lp-title mt-6 max-w-4xl text-[2.5rem] sm:text-5xl lg:text-6xl">{{ $service->name }}</h1>
            <p class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[15px] text-grafito-500">
                @if ($rating)
                    <a href="#resenas" class="flex items-center gap-1.5 text-grafito"><x-site.icon name="star" class="h-4 w-4 text-oro-500" /> {{ number_format($rating, 1) }} <span class="text-grafito-500">· {{ $service->reviews_count }} {{ $service->reviews_count === 1 ? 'reseña' : 'reseñas' }}</span></a>
                @else
                    <span>Sin reseñas todavía</span>
                @endif
                @if ($isProvider && $provider && $provider->zones->isNotEmpty())
                    <span class="flex items-center gap-1.5"><x-site.icon name="pin" class="h-4 w-4" /> {{ $provider->zones->map->label()->implode(' · ') }}</span>
                @elseif (! $isProvider)
                    <span class="flex items-center gap-1.5"><x-site.icon name="pin" class="h-4 w-4" /> Todas las zonas</span>
                @endif
                <span class="flex items-center gap-1.5"><x-site.icon name="eye" class="h-4 w-4" /> {{ number_format($views) }} {{ $views === 1 ? 'visita' : 'visitas' }}</span>
            </p>
        </section>

        <section class="mx-auto grid max-w-[84rem] gap-10 px-5 pb-24 pt-10 sm:px-8 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-14">
            {{-- Columna principal --}}
            <div class="min-w-0">
                <div class="aspect-[16/10] overflow-hidden rounded-[28px] bg-humo-200">
                    <img src="{{ \App\Support\Media::url('services', $service->image) }}" alt="{{ $service->name }}" class="h-full w-full object-cover">
                </div>

                @if ($service->tagline)
                    <p class="lp-title mt-10 text-2xl leading-snug sm:text-3xl">{{ $service->tagline }}</p>
                @endif
                @if ($service->description)
                    <p class="mt-5 whitespace-pre-line text-[17px] leading-relaxed text-grafito-600">{{ $service->description }}</p>
                @endif

                @php
                    $inclusions = array_filter(explode('|', (string) $service->inclusion));
                    $exclusions = array_filter(explode('|', (string) $service->exclusion));
                @endphp
                @if ($inclusions || $exclusions)
                    <div class="mt-12 grid gap-8 border-t border-humo-300 pt-10 sm:grid-cols-2">
                        @if ($inclusions)
                            <div>
                                <h2 class="text-[15px] font-medium">Qué incluye</h2>
                                <ul class="mt-4 space-y-3">
                                    @foreach ($inclusions as $inclusion)
                                        <li class="flex gap-3 text-[15px] text-grafito-600"><x-site.icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" /> {{ $inclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($exclusions)
                            <div>
                                <h2 class="text-[15px] font-medium">Qué no incluye</h2>
                                <ul class="mt-4 space-y-3">
                                    @foreach ($exclusions as $exclusion)
                                        <li class="flex gap-3 text-[15px] text-grafito-600"><x-site.icon name="close" class="mt-0.5 h-5 w-5 shrink-0 text-grafito-400" /> {{ $exclusion }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Reseñas --}}
                <div id="resenas" class="mt-14 scroll-mt-28 border-t border-humo-300 pt-10">
                    <h2 class="lp-title text-3xl">Lo que dicen <span class="lp-accent">los clientes</span></h2>
                    @forelse ($reviews as $review)
                        <figure class="border-b border-humo-200 py-6 last:border-b-0">
                            <div class="flex flex-wrap items-center gap-3 text-[14px]">
                                <span class="font-medium">{{ \Illuminate\Support\Str::before((string) $review->user?->name, ' ') ?: 'Cliente' }}</span>
                                <span class="flex text-oro-500" aria-label="{{ $review->rating }} de 5 estrellas">
                                    @for ($i = 1; $i <= 5; $i++)<x-site.icon name="star" class="h-4 w-4 {{ $i <= $review->rating ? '' : 'opacity-25' }}" />@endfor
                                </span>
                                <span class="text-grafito-400">{{ $review->created_at->format('d/m/Y') }}</span>
                            </div>
                            @if ($review->comment)
                                <blockquote class="mt-2 text-[16px] leading-relaxed text-grafito-600">{{ $review->comment }}</blockquote>
                            @endif
                        </figure>
                    @empty
                        <p class="mt-4 text-[15px] text-grafito-500">Aún no hay reseñas. Solo pueden opinar los clientes que ya recibieron este trabajo, así que lo que leas aquí será real.</p>
                    @endforelse
                </div>
            </div>

            {{-- Columna lateral: precio, contacto y solicitud --}}
            <aside class="space-y-6">
                <div class="ui-card ui-card-pad">
                    <p class="text-[14px] text-grafito-500">Precio desde</p>
                    <p class="mt-1 flex items-baseline gap-3">
                        <span class="lp-title text-5xl">${{ number_format($service->finalPrice(), 2) }}</span>
                        @if ($service->discount)
                            <span class="text-[15px] text-grafito-400 line-through">${{ number_format((float) $service->price, 2) }}</span>
                        @endif
                    </p>
                    <p class="mt-3 text-[14px] leading-snug text-grafito-500">Es un precio de referencia. El precio final lo acuerdas con el proveedor según el trabajo, y le pagas directo a él.</p>

                    <div class="mt-6 grid gap-3">
                        @if ($whatsapp)
                            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-lg">
                                <x-site.icon name="whatsapp" class="h-5 w-5" /> Escribir por WhatsApp
                            </a>
                        @endif
                        <a href="#solicitar" class="ui-btn ui-btn-secondary ui-btn-lg">Solicitar este servicio</a>
                    </div>
                    <p class="mt-4 text-[13px] leading-snug text-grafito-500">WhatsApp es lo más rápido. La solicitud deja todo por escrito y te permite calificar al final.</p>
                </div>

                @if ($isProvider)
                    <div class="ui-card ui-card-pad">
                        <p class="text-[13px] font-medium uppercase tracking-[.08em] text-grafito-400">Quién hace el trabajo</p>
                        <div class="mt-4 flex items-center gap-4">
                            <img src="{{ \App\Support\Media::url('sproviders', ($provider?->image ?: 'default.jpg')) }}" alt="" class="h-14 w-14 rounded-full object-cover">
                            <div>
                                <p class="text-[17px] font-medium">{{ $service->user->name }}</p>
                                @if ($provider?->city)<p class="text-[14px] text-grafito-500">{{ $provider->city }}</p>@endif
                            </div>
                        </div>
                        @if ($provider?->about)
                            <p class="mt-4 text-[15px] leading-relaxed text-grafito-600">{{ $provider->about }}</p>
                        @endif
                    </div>
                @endif

                <div id="solicitar" class="ui-card ui-card-pad scroll-mt-28">
                    <h2 class="lp-title text-2xl">Solicitar este servicio</h2>

                    @if (Session::has('request_sent'))
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-[15px] text-emerald-950" role="status">
                            <p class="flex items-center gap-2 font-medium"><x-site.icon name="check-circle" class="h-5 w-5 text-emerald-700" /> ¡Solicitud enviada!</p>
                            <p class="mt-2 leading-relaxed">{{ Session::get('request_sent') }}</p>
                            <a href="{{ route('customer.dashboard') }}" class="ui-btn ui-btn-primary ui-btn-sm mt-4">Ver mis solicitudes <x-site.icon name="arrow-right" class="h-4 w-4" /></a>
                        </div>
                    @endif

                    @guest
                        <p class="mt-3 text-[15px] leading-relaxed text-grafito-500">Para enviar una solicitud necesitas una cuenta (es gratis y toma un minuto). Así el proveedor sabe quién eres y luego puedes calificarlo.</p>
                        <div class="mt-5 grid gap-3">
                            <a href="{{ route('register') }}" @click="if (window.lpAuth) { $event.preventDefault(); $dispatch('open-auth', { mode: 'register', as: 'CST' }) }" class="ui-btn ui-btn-primary">Crear mi cuenta gratis</a>
                            <a href="{{ route('login') }}" @click="if (window.lpAuth) { $event.preventDefault(); $dispatch('open-auth', { mode: 'login' }) }" class="ui-btn ui-btn-secondary">Ya tengo cuenta: ingresar</a>
                        </div>
                    @else
                        @if ($isOwnService)
                            <p class="mt-3 text-[15px] leading-relaxed text-grafito-500">Este servicio es tuyo, así que no puedes solicitarlo. Así lo ven tus clientes.</p>
                            <a href="{{ route($authUser->utype === 'ADM' ? 'admin.edit_service' : 'sprovider.edit_service', ['service_slug' => $service->slug]) }}" class="ui-btn ui-btn-secondary mt-5"><x-site.icon name="edit" class="h-4 w-4" /> Editar este servicio</a>
                        @else
                            <p class="mt-2 text-[14px] text-grafito-500">Llena estos datos y el proveedor te contactará.</p>
                            <form wire:submit="submitRequest" class="mt-6 space-y-5">
                                <x-ui.field label="Tu nombre" name="name" for="req-name">
                                    <input type="text" id="req-name" class="ui-input @error('name') ui-input-invalid @enderror" wire:model="name" autocomplete="name">
                                </x-ui.field>
                                <x-ui.field label="Tu celular (WhatsApp)" name="phone" for="req-phone" hint="El proveedor te escribirá a este número.">
                                    <input type="tel" id="req-phone" class="ui-input @error('phone') ui-input-invalid @enderror" wire:model="phone" placeholder="0991234567" inputmode="tel" autocomplete="tel">
                                </x-ui.field>
                                <x-ui.field label="¿En qué zona?" name="zone" for="req-zone">
                                    <select id="req-zone" class="ui-input @error('zone') ui-input-invalid @enderror" wire:model="zone">
                                        <option value="">Elige tu zona</option>
                                        @foreach ($zones as $slug => $label)
                                            <option value="{{ $slug }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </x-ui.field>
                                <x-ui.field label="Dirección o referencia" name="address" for="req-address" optional>
                                    <input type="text" id="req-address" class="ui-input @error('address') ui-input-invalid @enderror" wire:model="address" placeholder="Barrio, quinta, cerca de…">
                                </x-ui.field>
                                <x-ui.field label="¿Para qué día?" name="preferred_date" for="req-date" optional>
                                    <input type="date" id="req-date" class="ui-input @error('preferred_date') ui-input-invalid @enderror" wire:model="preferred_date" min="{{ now()->toDateString() }}">
                                </x-ui.field>
                                <x-ui.field label="¿Qué necesitas?" name="message" for="req-message" optional hint="Ej.: se rompió la llave del lavamanos y gotea.">
                                    <textarea id="req-message" rows="3" class="ui-input @error('message') ui-input-invalid @enderror" wire:model="message"></textarea>
                                </x-ui.field>
                                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full" wire:loading.attr="disabled" wire:target="submitRequest">
                                    <span wire:loading.remove wire:target="submitRequest">Enviar solicitud</span>
                                    <span wire:loading wire:target="submitRequest">Enviando…</span>
                                </button>
                            </form>
                        @endif
                    @endguest
                </div>

                @if ($r_service)
                    <a href="{{ route('home.service_details', ['service_slug' => $r_service->slug]) }}" class="ui-card group flex items-center gap-4 p-4 hover:border-grafito-400">
                        <img src="{{ \App\Support\Media::url('services/thumbnails', $r_service->thumbnail) }}" alt="" loading="lazy" class="h-16 w-16 shrink-0 rounded-xl object-cover">
                        <span class="min-w-0 flex-1">
                            <span class="block text-[12px] uppercase tracking-[.08em] text-grafito-400">También te puede servir</span>
                            <span class="mt-0.5 block truncate text-[15px] font-medium">{{ $r_service->name }}</span>
                            <span class="block text-[13px] text-grafito-500">Desde ${{ number_format((float) $r_service->price, 2) }}</span>
                        </span>
                        <x-site.icon name="arrow-right" class="h-4 w-4 text-grafito-400 transition group-hover:translate-x-0.5" />
                    </a>
                @endif
            </aside>
        </section>
    @else
        <section class="mx-auto max-w-[84rem] px-5 py-20 sm:px-8">
            <x-ui.empty icon="search" title="Este servicio ya no está disponible">
                Puede que el proveedor lo haya quitado o que el enlace esté mal escrito. Busca otro parecido.
                <x-slot:actions>
                    <a href="{{ route('search') }}" class="ui-btn ui-btn-primary">Buscar servicios</a>
                    <a href="{{ route('home.service_categories') }}" class="ui-btn ui-btn-secondary">Ver todos los oficios</a>
                </x-slot:actions>
            </x-ui.empty>
        </section>
    @endif
</div>
