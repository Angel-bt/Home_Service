{{-- Landing "Valle de noche". Datos: $landing (HomeComponent). Animaciones: resources/js/landing.js (atributos data-*). --}}
@php
    $stats = $landing['stats'];
    $zones = $landing['zones'];
    $categories = $landing['categories'];
    $featured = $landing['featured'];
    $reviews = $landing['reviews'];
    $ticker = $landing['ticker'];
    // Fotos de Unsplash: AVIF/WebP automático y calidad 50 (van bajo un velo oscuro o en tamaño medio).
    $photo = fn (string $id, int $w) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=50";
    $authUser = auth()->user();
    $registerAttrs = 'href="' . route('register') . '" @click="if (window.lpAuth) { $event.preventDefault(); $dispatch(\'open-auth\', { mode: \'register\', as: \'SVP\' }) }"';
    $plural = fn (int $n, string $one, string $many) => $n . ' ' . ($n === 1 ? $one : $many);
@endphp
<div>
    {{-- ================================ HERO ================================ --}}
    <section data-tone="dark" class="lp-noise relative isolate flex min-h-[100svh] flex-col overflow-hidden bg-noche-900 text-white">
        <div data-hero-media class="absolute inset-0 -z-10">
            <img src="{{ $photo('1616547142063-6d0d6f8668ca', 1600) }}"
                 srcset="{{ $photo('1616547142063-6d0d6f8668ca', 800) }} 800w, {{ $photo('1616547142063-6d0d6f8668ca', 1280) }} 1280w, {{ $photo('1616547142063-6d0d6f8668ca', 1600) }} 1600w"
                 sizes="100vw" alt="El valle visto desde la montaña, con el pueblo al fondo" fetchpriority="high"
                 class="h-full w-full object-cover object-center">
        </div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-noche-950/70 via-noche-900/45 to-noche-900" aria-hidden="true"></div>

        <div class="mx-auto flex w-full max-w-[84rem] flex-1 flex-col justify-center px-5 pb-10 pt-28 text-center sm:px-8">
            <p data-hero-eyebrow class="lp-eyebrow uppercase tracking-[.22em] text-white/60">Malacatos — Loja, Ecuador</p>
            <h1 data-hero-title class="lp-title mx-auto mt-7 max-w-5xl text-[2.75rem] sm:text-7xl lg:text-[5.5rem]">
                Gente de oficio de confianza, <span class="lp-accent text-oro-300">en Malacatos.</span>
            </h1>
            <p data-hero-lede class="mx-auto mt-6 max-w-xl text-[17px] leading-relaxed text-white/70">
                Gasfiteros, electricistas, jardineros y más de Malacatos, Vilcabamba, Yangana y Quinara. Les escribes directo y les pagas a ellos.
            </p>

            <form data-hero-search action="{{ route('search') }}" method="GET" role="search"
                  x-data="{
                      q: '', results: [], open: false, active: -1, timer: null,
                      suggest() {
                          clearTimeout(this.timer);
                          if (this.q.trim().length < 2) { this.results = []; this.open = false; return; }
                          this.timer = setTimeout(async () => {
                              try {
                                  const r = await fetch('{{ route('autocomplete') }}?query=' + encodeURIComponent(this.q.trim()), { headers: { Accept: 'application/json' } });
                                  this.results = (await r.json()).map(i => i.name).slice(0, 6);
                                  this.open = this.results.length > 0; this.active = -1;
                              } catch (e) { this.open = false; }
                          }, 180);
                      },
                      pick(name) { this.q = name; this.open = false; this.$nextTick(() => this.$root.submit()); },
                      move(step) { if (!this.open) return; this.active = (this.active + step + this.results.length) % this.results.length; },
                      enter(e) { if (this.open && this.active > -1) { e.preventDefault(); this.pick(this.results[this.active]); } },
                  }"
                  @click.outside="open = false"
                  class="lp-glass relative mx-auto mt-10 w-full max-w-2xl rounded-[28px] p-1.5 text-left sm:rounded-full">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center">
                    <label class="flex flex-1 items-center gap-3 px-5 py-3">
                        <x-site.icon name="search" class="h-5 w-5 shrink-0 text-white/60" />
                        <span class="sr-only">¿Qué necesitas?</span>
                        <input type="text" name="q" x-model="q" @input="suggest()" @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter="enter($event)" @keydown.escape="open = false"
                               autocomplete="off" placeholder="¿Qué necesitas arreglar?"
                               class="w-full border-0 bg-transparent p-0 text-[16px] text-white placeholder-white/50 focus:outline-none focus:ring-0"
                               role="combobox" aria-autocomplete="list" :aria-expanded="open" aria-controls="lp-suggestions">
                    </label>
                    <span class="mx-5 h-px bg-white/15 sm:mx-0 sm:h-6 sm:w-px" aria-hidden="true"></span>
                    <label class="flex items-center gap-2 px-5 py-3 sm:w-56">
                        <x-site.icon name="pin" class="h-5 w-5 shrink-0 text-white/60" />
                        <span class="sr-only">¿Dónde?</span>
                        <select name="zona" class="w-full cursor-pointer border-0 bg-transparent p-0 pr-6 text-[16px] text-white focus:outline-none focus:ring-0 [&>option]:text-grafito">
                            <option value="">Todas las zonas</option>
                            @foreach ($zones as $zone)
                                <option value="{{ $zone['slug'] }}" @selected(session('zone') === $zone['slug'])>{{ $zone['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" data-magnetic class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-6 py-3.5 text-[13px] font-medium uppercase tracking-[.08em] text-noche-900 transition hover:bg-oro-300">
                        Buscar <x-site.icon name="arrow-right" class="h-4 w-4" />
                    </button>
                </div>
                <ul x-cloak x-show="open" x-transition.opacity id="lp-suggestions" role="listbox"
                    class="absolute inset-x-0 top-full z-30 mt-2 overflow-hidden rounded-2xl bg-white p-1.5 text-grafito shadow-2xl">
                    <template x-for="(name, i) in results" :key="name">
                        <li role="option" :aria-selected="i === active">
                            <button type="button" @click="pick(name)" @mouseenter="active = i" :class="i === active && 'bg-humo-100'" class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-left text-[15px]">
                                <span x-text="name"></span> <x-site.icon name="arrow-up-right" class="h-4 w-4 opacity-40" />
                            </button>
                        </li>
                    </template>
                </ul>
            </form>
        </div>

        {{-- Cinta de servicios: se desplaza sola (CSS .lp-marquee) y se pausa con el cursor. La lista se repite hasta
             llenar el ancho y se duplica para que el bucle no tenga cortes; la copia queda oculta a lectores de pantalla. --}}
        @if ($ticker !== [])
            @php
                $tickerLoop = $ticker;
                while (count($tickerLoop) < 10) { $tickerLoop = array_merge($tickerLoop, $ticker); }
            @endphp
            <div data-hero-services class="lp-marquee relative mb-8" style="--lp-marquee-duration: {{ count($tickerLoop) * 3 }}s" aria-label="Servicios publicados">
                <div class="lp-marquee-track">
                    @foreach ([false, true] as $copy)
                        <ul class="flex shrink-0" @if ($copy) aria-hidden="true" @endif>
                            @foreach ($tickerLoop as $item)
                                <li class="mr-3 shrink-0">
                                    <a href="{{ route('home.service_details', ['service_slug' => $item['slug']]) }}" @if ($copy) tabindex="-1" @endif
                                       class="group flex w-[260px] items-center gap-3.5 rounded-2xl border border-white/10 bg-noche-950/40 p-2 pr-4 backdrop-blur-md transition hover:border-white/25 hover:bg-noche-950/60 sm:w-[300px]">
                                        <img src="{{ \App\Support\Media::url('services/thumbnails', $item['thumbnail']) }}" alt="" loading="lazy" width="64" height="64"
                                             class="h-16 w-16 shrink-0 rounded-xl object-cover">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[15px] font-medium text-white">{{ $item['name'] }}</span>
                                            <span class="mt-0.5 block truncate text-[13px] text-white/50">{{ $item['category'] }}@if ($item['provider']) · {{ $item['provider'] }}@endif</span>
                                            <span class="mt-1 flex items-center gap-3 text-[13px]">
                                                <span class="text-white/80">Desde ${{ number_format($item['price'], 2) }}</span>
                                                @if ($item['rating'])<span class="text-oro-300">★ {{ number_format($item['rating'], 1) }}</span>@endif
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Zonas: Malacatos primero, como zona principal --}}
        <div class="mx-auto w-full max-w-[84rem] px-5 pb-6 sm:px-8">
            <div data-hero-zones class="grid grid-cols-2 border-t border-white/15 lg:grid-cols-4">
                @foreach ($zones as $i => $zone)
                    <a href="{{ route('search', ['zona' => $zone['slug']]) }}"
                       class="group relative flex items-start justify-between gap-3 border-white/15 py-6 pr-4 {{ $i < 2 ? 'border-b lg:border-b-0' : '' }} {{ $i % 2 ? 'border-l pl-4 sm:pl-6' : '' }} {{ $i > 0 ? 'lg:border-l lg:pl-6' : '' }}">
                        <span>
                            <span class="flex items-center gap-2 text-[17px] font-medium">
                                {{ $zone['label'] }}
                                @if ($zone['main'])<span class="text-[11px] font-medium uppercase tracking-[.14em] text-oro-300">Principal</span>@endif
                            </span>
                            <span class="mt-1 block text-[13px] text-white/50">{{ $zone['note'] }} · {{ $zone['providers'] > 0 ? $plural($zone['providers'], 'proveedor', 'proveedores') : 'servicios del valle' }}</span>
                        </span>
                        <x-site.icon name="arrow-up-right" class="mt-1 h-4 w-4 shrink-0 text-white/40 transition duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-oro-300" />
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================== MANIFIESTO + CIFRAS ============================== --}}
    <section data-tone="light" class="px-5 py-28 sm:px-8 lg:py-40">
        <div class="mx-auto grid max-w-[84rem] gap-10 lg:grid-cols-12">
            <div class="lg:col-span-3">
                <p class="lp-eyebrow uppercase text-grafito-400">Sobre Malakplace</p>
            </div>
            <p data-words class="lp-title text-[1.75rem] leading-[1.18] text-grafito sm:text-4xl lg:col-span-9 lg:text-[2.9rem]">
                Reunimos a la gente de oficio de Malacatos y sus parroquias vecinas. Encuentras a quien te queda cerca, lees lo que dicen otros vecinos y le escribes <span class="lp-accent">directo.</span>
            </p>
        </div>
        <dl class="mx-auto mt-24 grid max-w-[84rem] border-y border-humo-300 sm:grid-cols-3">
            @foreach ([
                ['providers', 'Proveedores en el valle', null],
                ['services', 'Servicios publicados', null],
                ['reviews', 'Reseñas de clientes', $stats['rating'] ? '★ ' . number_format($stats['rating'], 1) . ' de promedio' : null],
            ] as $i => [$key, $label, $extra])
                <div data-anim class="py-10 text-center {{ $i > 0 ? 'border-t border-humo-300 sm:border-l sm:border-t-0' : '' }}">
                    <dt class="sr-only">{{ $label }}</dt>
                    <dd class="lp-accent text-7xl text-grafito lg:text-8xl" data-count="{{ (int) $stats[$key] }}">{{ (int) $stats[$key] }}</dd>
                    <dd class="mt-3 text-[14px] text-grafito-500">{{ $label }}@if ($extra) · <span class="text-grafito">{{ $extra }}</span>@endif</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ============================== OFICIOS ============================== --}}
    <section id="oficios" data-tone="light" class="scroll-mt-24 px-5 pb-28 sm:px-8 lg:pb-40" x-data>
        <div class="mx-auto max-w-[84rem]">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <h2 data-split class="lp-title text-5xl text-grafito lg:text-7xl">¿Qué necesitas <span class="lp-accent">arreglar?</span></h2>
                <a href="{{ route('home.service_categories') }}" class="group inline-flex items-center gap-2 self-start text-[13px] font-medium uppercase tracking-[.08em] text-grafito sm:self-auto">
                    Ver todos los oficios <x-site.icon name="arrow-up-right" class="h-4 w-4 transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                </a>
            </div>

            <div class="mt-14 grid md:grid-cols-2 md:gap-x-16">
                @foreach ($categories as $i => $category)
                    <a data-row href="{{ route('home.services_by_category', ['category_slug' => $category['slug']]) }}"
                       @if ($category['preview']) data-preview="{{ \App\Support\Media::url('services/thumbnails', $category['preview']) }}" @endif
                       class="group flex items-center gap-5 border-t border-humo-300 py-6 {{ $loop->last ? 'border-b' : ($loop->remaining === 1 && $loop->count % 2 === 0 ? 'md:border-b' : '') }}">
                        <span class="w-8 text-[13px] tabular-nums text-grafito-400">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="lp-title flex-1 text-2xl text-grafito transition duration-300 group-hover:translate-x-2 lg:text-[1.75rem]">{{ $category['name'] }}</span>
                        <span class="hidden text-[13px] text-grafito-500 sm:inline">{{ $category['count'] > 0 ? $plural($category['count'], 'servicio', 'servicios') : 'Próximamente' }}</span>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-humo-300 text-grafito transition duration-300 group-hover:border-noche-900 group-hover:bg-noche-900 group-hover:text-white">
                            <x-site.icon name="arrow-up-right" class="h-4 w-4" />
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
        {{-- Vista previa que sigue al cursor (la mueve landing.js) --}}
        <div data-preview-box class="pointer-events-none fixed left-0 top-0 z-40 hidden h-48 w-64 overflow-hidden rounded-2xl opacity-0 shadow-2xl lg:block" aria-hidden="true">
            <img src="" alt="" class="h-full w-full object-cover">
        </div>
    </section>

    {{-- ============================== RECOMENDADOS (tarjetas que se apilan) ============================== --}}
    @if ($featured !== [])
        <section data-tone="dark" class="lp-noise relative bg-noche-900 px-3 py-28 text-white sm:px-5 lg:py-36">
            <div class="lp-glow pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="relative mx-auto max-w-[84rem] px-2 sm:px-3">
                <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                    <h2 data-split class="lp-title text-5xl lg:text-7xl">Trabajos <span class="lp-accent text-oro-300">recomendados</span></h2>
                    <p class="max-w-sm text-[15px] leading-relaxed text-white/55">Servicios con buenas reseñas y respuesta rápida en Malacatos y alrededores.</p>
                </div>
                <div class="mt-16 space-y-6">
                    @foreach ($featured as $service)
                        <a data-stack href="{{ route('home.service_details', ['service_slug' => $service['slug']]) }}"
                           class="group relative block h-[72vh] min-h-[460px] overflow-hidden rounded-[32px] lg:sticky lg:top-24">
                            <img src="{{ \App\Support\Media::url('services', $service['image']) }}" alt="{{ $service['name'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-[1.2s] group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-noche-950/90 via-noche-950/35 to-noche-950/10" aria-hidden="true"></div>
                            <div class="relative flex h-full flex-col justify-end p-7 sm:p-10 lg:p-14">
                                <p class="lp-eyebrow uppercase text-white/60">{{ $service['category'] }}@if ($service['zones']) · {{ $service['zones'] }}@endif</p>
                                <h3 class="lp-title mt-3 max-w-3xl text-4xl sm:text-5xl lg:text-6xl">{{ $service['name'] }}</h3>
                                <div class="mt-7 flex flex-wrap items-center gap-x-8 gap-y-4 border-t border-white/15 pt-6 text-[15px] text-white/75">
                                    @if ($service['provider'])<span>{{ $service['provider'] }}</span>@endif
                                    <span>Desde <span class="text-white">${{ number_format($service['price'], 2) }}</span></span>
                                    @if ($service['rating'])<span><span class="text-oro-300">★ {{ number_format($service['rating'], 1) }}</span> · {{ $plural($service['reviews'], 'reseña', 'reseñas') }}</span>@endif
                                    <span class="ml-auto inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-[13px] font-medium uppercase tracking-[.08em] text-noche-900 transition group-hover:bg-oro-300">
                                        Ver servicio <x-site.icon name="arrow-up-right" class="h-4 w-4" />
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================== CÓMO FUNCIONA ============================== --}}
    <section id="como-funciona" data-tone="light" class="scroll-mt-24 px-5 py-28 sm:px-8 lg:py-40">
        <div class="mx-auto max-w-[84rem]">
            <div class="grid gap-6 lg:grid-cols-12">
                <p class="lp-eyebrow uppercase text-grafito-400 lg:col-span-3">Cómo funciona</p>
                <h2 data-split class="lp-title text-5xl text-grafito lg:col-span-9 lg:text-7xl">Así de <span class="lp-accent">simple</span>, como preguntarle a un vecino.</h2>
            </div>
            <ol class="mt-20 grid gap-12 md:grid-cols-3 md:gap-10">
                @foreach ([
                    ['Busca el oficio', 'Escribe lo que necesitas o elige un oficio, y filtra por tu zona para ver a quien te queda cerca.'],
                    ['Compara con calma', 'Revisa precios y reseñas. Solo puede opinar quien recibió el trabajo, así que lo que lees es real.'],
                    ['Escríbele y listo', 'Contáctalo por WhatsApp o envía una solicitud. Acuerdan el precio y le pagas directo al terminar.'],
                ] as $i => [$title, $text])
                    <li data-anim class="border-t border-humo-300 pt-8">
                        <span class="lp-accent text-6xl text-grafito-400">0{{ $i + 1 }}</span>
                        <h3 class="lp-title mt-6 text-2xl text-grafito">{{ $title }}</h3>
                        <p class="mt-3 text-[15px] leading-relaxed text-grafito-500">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ============================== RESEÑA DESTACADA ============================== --}}
    @if ($reviews !== [])
        @php $quote = $reviews[0]; @endphp
        <section data-tone="light" class="px-5 pb-28 sm:px-8 lg:pb-40">
            <figure data-anim class="mx-auto max-w-4xl border-t border-humo-300 pt-16 text-center">
                <p class="text-oro-500" aria-label="{{ $quote['rating'] }} de 5 estrellas">{{ str_repeat('★', $quote['rating']) }}</p>
                <blockquote class="lp-title mt-6 text-3xl leading-tight text-grafito sm:text-4xl lg:text-5xl">
                    <span class="lp-accent">“</span>{{ $quote['comment'] }}<span class="lp-accent">”</span>
                </blockquote>
                <figcaption class="mt-8 text-[15px] text-grafito-500">
                    {{ $quote['author'] }}@if ($quote['slug']) · <a href="{{ route('home.service_details', ['service_slug' => $quote['slug']]) }}" class="text-grafito underline-offset-4 hover:underline">{{ $quote['service'] }}</a>@endif
                </figcaption>
            </figure>
        </section>
    @endif

    {{-- ============================== PROVEEDORES ============================== --}}
    <section id="proveedores" data-tone="light" class="scroll-mt-24 px-3 pb-24 sm:px-5">
        <div data-anim class="mx-auto grid max-w-[86rem] overflow-hidden rounded-[36px] bg-oro-400 text-noche-900 lg:grid-cols-2">
            <div class="p-8 sm:p-12 lg:p-16">
                <p class="lp-eyebrow uppercase text-noche-900/60">Para proveedores</p>
                <h2 class="lp-title mt-5 text-5xl lg:text-6xl">¿Trabajas en Malacatos? <span class="lp-accent">Que te encuentren.</span></h2>
                <ul class="mt-10 space-y-4 text-[16px]">
                    @foreach (['Publica ' . config('marketplace.free_service_limit') . ' servicios gratis', 'Los clientes te escriben a tu WhatsApp', 'Tú pones el precio y cobras directo'] as $benefit)
                        <li class="flex items-center gap-3 border-b border-noche-900/15 pb-4"><x-site.icon name="check" class="h-5 w-5" /> {{ $benefit }}</li>
                    @endforeach
                </ul>
                <div class="mt-10 flex flex-wrap gap-3">
                    @guest
                        <a {!! $registerAttrs !!} data-magnetic class="inline-flex items-center gap-2 rounded-full bg-noche-900 px-7 py-4 text-[13px] font-medium uppercase tracking-[.08em] text-white transition hover:bg-noche-700">
                            Empezar gratis <x-site.icon name="arrow-up-right" class="h-4 w-4" />
                        </a>
                    @else
                        <a href="{{ $authUser->utype === 'SVP' ? route('sprovider.add_service') : route('home.contact') }}" data-magnetic class="inline-flex items-center gap-2 rounded-full bg-noche-900 px-7 py-4 text-[13px] font-medium uppercase tracking-[.08em] text-white transition hover:bg-noche-700">
                            {{ $authUser->utype === 'SVP' ? 'Publicar un servicio' : 'Quiero ofrecer servicios' }} <x-site.icon name="arrow-up-right" class="h-4 w-4" />
                        </a>
                    @endguest
                    <a href="{{ route('home.faq') }}" class="inline-flex items-center rounded-full border border-noche-900/25 px-7 py-4 text-[13px] font-medium uppercase tracking-[.08em] transition hover:border-noche-900">Preguntas frecuentes</a>
                </div>
            </div>
            <div class="relative min-h-[320px] lg:min-h-full">
                <img src="{{ $photo('1640554214186-788574b31564', 1000) }}" alt="Montañas del sur de Loja con luz de tarde" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                <div class="lp-glass absolute bottom-6 left-6 right-6 rounded-2xl p-5 text-white sm:right-auto sm:w-72">
                    <p class="lp-accent text-4xl">{{ config('marketplace.free_service_limit') }} servicios</p>
                    <p class="mt-1 text-[13px] text-white/75">gratis para empezar · sin comisiones</p>
                </div>
            </div>
        </div>
    </section>
</div>
