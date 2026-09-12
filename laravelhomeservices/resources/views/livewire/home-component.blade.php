<div class="proyetech-home">
    <section class="proyetech-hero" aria-labelledby="hero-title">
        <div class="proyetech-hero-grid"></div>
        <div class="proyetech-hero-inner">
            <div class="proyetech-hero-copy">
                <p class="proyetech-kicker"><span class="proyetech-status-dot"></span> PROYETECH / RED DE SERVICIOS</p>
                <h1 id="hero-title">Servicios del hogar,<br><span>re-imaginados.</span></h1>
                <p class="proyetech-hero-lede">Encuentra especialistas verificados en Loja y Malacatos. Compara soluciones, conecta con profesionales y resuelve tu hogar con confianza.</p>
                <div class="proyetech-hero-actions">
                    <a class="proyetech-button proyetech-button-primary" href="{{ route('home.service_categories') }}"><span>Explorar servicios</span><i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                    <a class="proyetech-button proyetech-button-ghost" href="#proyetech-proof"><i class="fa fa-compass" aria-hidden="true"></i><span>Conocer la red</span></a>
                </div>
                <div class="proyetech-trust-line"><i class="fa fa-shield-halved" aria-hidden="true"></i> Profesionales verificados <span></span> Atención local <span></span> Respuesta rápida</div>
            </div>
            <div class="proyetech-hero-visual" aria-label="Interactive service network visualization">
                <div class="proyetech-orbit proyetech-orbit-one"></div>
                <div class="proyetech-orbit proyetech-orbit-two"></div>
                <canvas id="proyetech-network" width="620" height="620"></canvas>
                <div class="proyetech-visual-label proyetech-visual-label-top"><span>SERVICIOS ACTIVOS</span><strong>99.98%</strong></div>
                <div class="proyetech-visual-label proyetech-visual-label-bottom"><span>ESTADO DE LA RED</span><strong><i class="proyetech-status-dot"></i> DISPONIBLE</strong></div>
            </div>
        </div>
    </section>

    <section class="proyetech-search-panel" aria-label="Buscar servicios">
        <div class="proyetech-search-intro"><span class="proyetech-section-index">01</span><strong>Encuentra tu especialista</strong><small>Busca una solución para tu hogar</small></div>
        <form id="sform" class="proyetech-search-form" action="{{ route('searchService') }}" method="post">
            @csrf
            <label class="sr-only" for="q">¿Qué servicio necesitas?</label>
            <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
            <input type="text" id="q" name="q" required placeholder="¿Qué servicio necesitas?" class="typeahead" autocomplete="off">
            <button type="submit">Buscar <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
        </form>
    </section>

    <section id="proyetech-proof" class="proyetech-metrics" aria-label="Marketplace metrics">
        <div><strong>&lt; 10<span>min</span></strong><small>Tiempo de respuesta</small></div>
        <div><strong>24<span>/7</span></strong><small>Ayuda para tu hogar</small></div>
        <div><strong>4.9<span>/5</span></strong><small>Valoración promedio</small></div>
        <div><strong>100<span>%</span></strong><small>Profesionales verificados</small></div>
    </section>

    <section class="proyetech-home-section proyetech-category-strip" aria-labelledby="category-title">
        <div class="proyetech-section-heading"><div><p class="proyetech-kicker">02 / DESCUBRE</p><h2 id="category-title">Una red.<br><span>Cada solución.</span></h2></div><a href="{{ route('home.service_categories') }}" class="proyetech-text-link">Ver todas las categorías <i class="fa fa-arrow-right" aria-hidden="true"></i></a></div>
        <div class="proyetech-category-list">
            @forelse($scategories->take(6) as $scategory)
                <a class="proyetech-category-tile" href="{{ route('home.services_by_category', ['category_slug' => $scategory->slug]) }}">
                    <span class="proyetech-tile-icon"><img src="{{ asset('images/categories') }}/{{ $scategory->image }}" alt=""></span><span>{{ $scategory->name }}</span><i class="fa fa-arrow-right" aria-hidden="true"></i>
                </a>
            @empty
                <div class="proyetech-empty-state">Las categorías se están preparando para ti.</div>
            @endforelse
        </div>
    </section>

    <section class="proyetech-home-section proyetech-services-section" aria-labelledby="services-title">
        <div class="proyetech-section-heading"><div><p class="proyetech-kicker">03 / PARA TI</p><h2 id="services-title">Soluciones que<br><span>sí funcionan.</span></h2></div><span class="proyetech-live-pill"><i class="proyetech-status-dot"></i> Disponibilidad activa</span></div>
        <div class="proyetech-service-grid">
            @forelse($fservices->take(4) as $service)
                <a class="proyetech-service-card" href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}">
                    <div class="proyetech-service-image"><img src="{{ asset('images/services/thumbnails') }}/{{ $service->thumbnail }}" alt="{{ $service->name }}"><span>VERIFICADO</span></div>
                    <div class="proyetech-service-info"><span>{{ $service->category->name ?? 'Servicio para el hogar' }}</span><h3>{{ $service->name }}</h3><p>{{ $service->tagline }}</p><strong>Desde ${{ $service->price }} <i class="fa fa-arrow-right" aria-hidden="true"></i></strong></div>
                </a>
            @empty
                <div class="proyetech-empty-state">Aquí aparecerán los servicios destacados de nuestros profesionales.</div>
            @endforelse
        </div>
    </section>
</div>

@push('scripts')
<script>
    (function () {
        var canvas = document.getElementById('proyetech-network');
        if (!canvas) return;
        var context = canvas.getContext('2d');
        var points = [], pointer = { x: 0, y: 0 }, reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        for (var index = 0; index < 72; index += 1) {
            var angle = Math.acos(1 - (2 * (index + 0.5) / 72)), spin = Math.PI * (1 + Math.sqrt(5)) * index;
            points.push({ x: Math.sin(angle) * Math.cos(spin), y: Math.sin(angle) * Math.sin(spin), z: Math.cos(angle) });
        }
        function draw(time) {
            var width = canvas.width, center = width / 2, radius = width * .34, rotation = reducedMotion ? .25 : time * .00018 + pointer.x * .22;
            context.clearRect(0, 0, width, width);
            var projected = points.map(function (point) {
                var x = point.x * Math.cos(rotation) - point.z * Math.sin(rotation), z = point.x * Math.sin(rotation) + point.z * Math.cos(rotation), y = point.y * Math.cos(pointer.y * .12) - z * Math.sin(pointer.y * .12);
                return { x: center + x * radius, y: center + y * radius, z: z };
            }).sort(function (a, b) { return a.z - b.z; });
            context.lineWidth = 1;
            for (var link = 0; link < projected.length; link += 1) {
                for (var next = link + 1; next < projected.length; next += 1) {
                    var distance = Math.hypot(projected[link].x - projected[next].x, projected[link].y - projected[next].y);
                    if (distance < 58) { context.strokeStyle = 'rgba(0, 240, 255, ' + (.07 + Math.max(projected[link].z, projected[next].z) * .12) + ')'; context.beginPath(); context.moveTo(projected[link].x, projected[link].y); context.lineTo(projected[next].x, projected[next].y); context.stroke(); }
                }
                var alpha = .25 + (projected[link].z + 1) * .35; context.fillStyle = 'rgba(0, 240, 255, ' + alpha + ')'; context.beginPath(); context.arc(projected[link].x, projected[link].y, 1.5 + (projected[link].z + 1), 0, Math.PI * 2); context.fill();
            }
            if (!reducedMotion) window.requestAnimationFrame(draw);
        }
        canvas.addEventListener('pointermove', function (event) { var bounds = canvas.getBoundingClientRect(); pointer.x = (event.clientX - bounds.left) / bounds.width - .5; pointer.y = (event.clientY - bounds.top) / bounds.height - .5; });
        draw(0);
    }());
</script>
<script>
    var path = "{{ route('autocomplete') }}";
    if (window.jQuery && $.fn.typeahead) { $('input.typeahead').typeahead({ source: function (query, process) { return $.get(path, { query: query }, process); } }); }
</script>
@endpush
