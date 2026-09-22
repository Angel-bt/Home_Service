<div class="proyetech-home">
    <section class="proyetech-hero" aria-labelledby="hero-title">
        <div class="proyetech-hero-grid"></div>
        <div class="proyetech-hero-inner">
            <div class="proyetech-hero-copy">
                <p class="proyetech-kicker"><span class="proyetech-status-dot"></span> PROYETECH / RED DE SERVICIOS</p>
                <h1 id="hero-title">Encuentra al mejor<br><span class="proyetech-hero-profession" id="hero-profession">Arquitecto</span><br><small style="font-size:.42em;font-weight:600;color:#66758b;letter-spacing:-.01em;">para tu proyecto, en un solo lugar.</small></h1>
                <p class="proyetech-hero-lede" id="hero-lede">Especialistas verificados en Malacatos. Compara soluciones, conecta con el profesional ideal y resuelve tu hogar con confianza.</p>
                <div class="proyetech-hero-actions">
                    <a class="proyetech-button proyetech-button-primary" href="{{ route('home.service_categories') }}"><span>Explorar servicios</span><i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                    <a class="proyetech-button proyetech-button-ghost" href="#proyetech-proof"><i class="fa fa-compass" aria-hidden="true"></i><span>Conocer la red</span></a>
                </div>
                <div class="proyetech-trust-line"><i class="fa fa-shield-halved" aria-hidden="true"></i> Profesionales verificados <span></span> Atención local <span></span> Respuesta rápida</div>
            </div>
            <div class="proyetech-hero-visual" aria-label="Profesionales de la red" data-hero-slider>
                <div class="proyetech-hero-visual-media" id="proyetech-hero-frames" aria-hidden="true">
                    {{-- Imágenes de profesiones con el MISMO encuadre (800x1000) para evitar saltos visuales.
                         En producción sustituye estos .svg por .webp con el mismo tamaño y cambia la extensión. --}}
                    <img class="proyetech-hero-frame is-active" src="{{ asset('images/hero/architect.svg') }}" alt="" loading="eager" decoding="async">
                    <img class="proyetech-hero-frame" src="{{ asset('images/hero/doctor.svg') }}" alt="" loading="lazy" decoding="async">
                    <img class="proyetech-hero-frame" src="{{ asset('images/hero/developer.svg') }}" alt="" loading="lazy" decoding="async">
                    <img class="proyetech-hero-frame" src="{{ asset('images/hero/electrician.svg') }}" alt="" loading="lazy" decoding="async">
                </div>
                <div id="proyetech-3d" role="img" aria-label="Iconos flotantes de oficios del hogar"></div>
                <div class="proyetech-hero-dots" id="proyetech-hero-dots" role="tablist" aria-label="Selector de profesión"></div>
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
                {{-- Click abre el modal dedicado; el href se mantiene como fallback accesible. --}}
                <a class="proyetech-category-tile" href="{{ route('home.services_by_category', ['category_slug' => $scategory->slug]) }}"
                   data-cat-trigger
                   data-cat-slug="{{ $scategory->slug }}"
                   data-cat-name="{{ $scategory->name }}"
                   data-cat-image="{{ asset('images/categories') }}/{{ $scategory->image }}"
                   aria-haspopup="dialog">
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

{{-- MEJORA: modal independiente por categoría (subcategorías, destacados, filtro, cerrar/explorar). --}}
<div id="proyetech-cat-modal" class="proyetech-cat-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="proyetech-cat-title" data-category-modals='@json($categoryModals)'>
    <div class="proyetech-cat-dialog" role="document">
        <div class="proyetech-cat-head">
            <span class="proyetech-cat-icon"><img id="proyetech-cat-img" src="" alt=""></span>
            <div class="proyetech-cat-head-copy">
                <p>CATEGORÍA / EXPLORAR</p>
                <h3 id="proyetech-cat-title">Categoría</h3>
            </div>
            <button type="button" class="proyetech-cat-close" id="proyetech-cat-close" aria-label="Cerrar"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="proyetech-cat-body">
            <label class="proyetech-cat-search" for="proyetech-cat-filter">
                <i class="fa fa-magnifying-glass" aria-hidden="true"></i>
                <input type="text" id="proyetech-cat-filter" placeholder="Filtra profesionales de esta categoría…" autocomplete="off">
            </label>
            <div class="proyetech-cat-block">
                <p class="proyetech-cat-block-title">Subcategorías / servicios</p>
                <div class="proyetech-cat-chips" id="proyetech-cat-chips"></div>
            </div>
            <div class="proyetech-cat-block">
                <p class="proyetech-cat-block-title">Profesionales destacados</p>
                <div class="proyetech-cat-pros" id="proyetech-cat-pros"></div>
            </div>
        </div>
        <div class="proyetech-cat-foot">
            <button type="button" class="proyetech-button proyetech-button-ghost" id="proyetech-cat-fullscreen"><i class="fa fa-expand" aria-hidden="true"></i><span>Pantalla completa</span></button>
            <a class="proyetech-cat-explore" id="proyetech-cat-explore" href="#"><span>Explorar categoría</span><i class="fa fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</div>

{{-- MEJORA: módulo unificado de autenticación (Login + Registro en una sola ventana). --}}
<div id="proyetech-auth-modal" class="proyetech-auth-modal" data-active="login" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="proyetech-auth-heading">
    <div class="proyetech-auth-dialog" role="document">
        <button type="button" class="proyetech-auth-close" id="proyetech-auth-close" aria-label="Cerrar"><i class="fa fa-times" aria-hidden="true"></i></button>
        <div class="proyetech-auth-banner">
            <p>ACCESO PROYETECH</p>
            <h3 id="proyetech-auth-heading">Tu red de especialistas</h3>
        </div>

        <div class="proyetech-auth-tabs" id="proyetech-auth-tabs" data-active="login">
            <span class="proyetech-auth-indicator" aria-hidden="true"></span>
            <button type="button" class="proyetech-auth-tab is-active" data-auth-tab="login">Iniciar sesión</button>
            <button type="button" class="proyetech-auth-tab" data-auth-tab="register">Crear cuenta</button>
        </div>

        <div class="proyetech-auth-panes">
            <div class="proyetech-auth-track">
                {{-- Panel: Iniciar sesión (Fortify POST /login) --}}
                <div class="proyetech-auth-pane" data-auth-pane="login">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="proyetech-auth-field">
                            <label for="proyetech-login-email">Correo electrónico</label>
                            <input class="proyetech-auth-input" id="proyetech-login-email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                        </div>
                        <div class="proyetech-auth-field">
                            <label for="proyetech-login-password">Contraseña</label>
                            <input class="proyetech-auth-input" id="proyetech-login-password" type="password" name="password" required autocomplete="current-password">
                        </div>
                        <div class="proyetech-auth-row">
                            <label><input type="checkbox" name="remember"> Recordarme</label>
                            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        </div>
                        <button type="submit" class="proyetech-auth-submit">Entrar</button>
                    </form>
                    <div class="proyetech-auth-divider">o continúa con</div>
                    <div class="proyetech-auth-social">
                        <button type="button" data-social="google" title="Próximamente"><i class="fab fa-google" aria-hidden="true"></i> Google</button>
                        <button type="button" data-social="apple" title="Próximamente"><i class="fab fa-apple" aria-hidden="true"></i> Apple</button>
                    </div>
                    <p class="proyetech-auth-alt">¿Aún no tienes cuenta? <button type="button" data-auth-goto="register">Regístrate gratis</button></p>
                </div>

                {{-- Panel: Crear cuenta (Fortify POST /register) --}}
                <div class="proyetech-auth-pane" data-auth-pane="register">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="proyetech-auth-field">
                            <label for="proyetech-reg-name">Nombre completo</label>
                            <input class="proyetech-auth-input" id="proyetech-reg-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
                        </div>
                        <div class="proyetech-auth-field">
                            <label for="proyetech-reg-email">Correo electrónico</label>
                            <input class="proyetech-auth-input" id="proyetech-reg-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                        </div>
                        <div class="proyetech-auth-field">
                            <label for="proyetech-reg-phone">Teléfono</label>
                            <input class="proyetech-auth-input" id="proyetech-reg-phone" type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel">
                        </div>
                        <div class="proyetech-auth-field">
                            <label for="proyetech-reg-password">Contraseña</label>
                            <input class="proyetech-auth-input" id="proyetech-reg-password" type="password" name="password" required autocomplete="new-password">
                        </div>
                        <div class="proyetech-auth-field">
                            <label for="proyetech-reg-password-confirm">Confirmar contraseña</label>
                            <input class="proyetech-auth-input" id="proyetech-reg-password-confirm" type="password" name="password_confirmation" required autocomplete="new-password">
                        </div>
                        <div class="proyetech-auth-field">
                            <label for="proyetech-reg-as">Quiero registrarme como</label>
                            <select class="proyetech-auth-input" id="proyetech-reg-as" name="registeras">
                                <option value="CST">Cliente</option>
                                <option value="SVP">Proveedor de servicios</option>
                            </select>
                        </div>
                        <button type="submit" class="proyetech-auth-submit">Crear cuenta</button>
                    </form>
                    <p class="proyetech-auth-alt">¿Ya tienes cuenta? <button type="button" data-auth-goto="login">Inicia sesión</button></p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    (function () {
        var wrap = document.getElementById('proyetech-3d');
        if (!wrap || typeof THREE === 'undefined') return;

        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var scene = new THREE.Scene();
        var camera = new THREE.PerspectiveCamera(60, wrap.clientWidth / wrap.clientHeight, 0.1, 100);
        camera.position.z = 7;
        var renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(wrap.clientWidth, wrap.clientHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        wrap.appendChild(renderer.domElement);

        scene.add(new THREE.AmbientLight(0xffffff, 0.85));
        var spark = new THREE.PointLight(0x00f0ff, 1.6);
        spark.position.set(4, 3, 5);
        scene.add(spark);
        var warm = new THREE.PointLight(0xf97316, 1.1);
        warm.position.set(-5, -2, 4);
        scene.add(warm);

        // Materiales con la paleta del tema actual (navy/cyan + naranja cálido).
        var cyan = new THREE.MeshStandardMaterial({ color: 0x00f0ff, metalness: 0.55, roughness: 0.3 });
        var navy = new THREE.MeshStandardMaterial({ color: 0x0e1a33, metalness: 0.7, roughness: 0.25 });
        var orange = new THREE.MeshStandardMaterial({ color: 0xf97316, metalness: 0.35, roughness: 0.4 });

        // Constructores de oficios a partir de primitivas.
        function screwdriver() {
            var g = new THREE.Group();
            var shaft = new THREE.Mesh(new THREE.CylinderGeometry(0.06, 0.06, 1.25, 12), cyan); shaft.position.y = 0.25;
            var handle = new THREE.Mesh(new THREE.CylinderGeometry(0.16, 0.2, 0.5, 12), navy); handle.position.y = -0.6;
            g.add(shaft, handle); g.rotation.z = -Math.PI / 2; return g;
        }
        function wrench() {
            var g = new THREE.Group();
            var ring = new THREE.Mesh(new THREE.TorusGeometry(0.26, 0.09, 10, 22), orange);
            var shank = new THREE.Mesh(new THREE.BoxGeometry(0.1, 0.95, 0.1), orange); shank.position.y = -0.5;
            var jaw = new THREE.Mesh(new THREE.BoxGeometry(0.34, 0.2, 0.09), orange); jaw.position.y = -1.02;
            g.add(ring, shank, jaw); return g;
        }
        function broom() {
            var g = new THREE.Group();
            var stick = new THREE.Mesh(new THREE.CylinderGeometry(0.05, 0.05, 1.6, 10), navy); stick.position.y = 0.5;
            var head = new THREE.Mesh(new THREE.BoxGeometry(0.5, 0.28, 0.16), orange); head.position.y = -0.4;
            var bristles = new THREE.Mesh(new THREE.BoxGeometry(0.46, 0.34, 0.14), cyan); bristles.position.y = -0.64;
            g.add(stick, head, bristles); return g;
        }
        function vacuum() {
            var g = new THREE.Group();
            var body = new THREE.Mesh(new THREE.BoxGeometry(0.7, 0.4, 0.5), cyan); body.position.y = -0.25;
            var tank = new THREE.Mesh(new THREE.CylinderGeometry(0.22, 0.26, 0.45, 14), navy); tank.position.set(0.32, 0.28, 0);
            var hose = new THREE.Mesh(new THREE.TorusGeometry(0.16, 0.045, 8, 18), orange); hose.position.set(-0.2, 0.35, 0);
            g.add(body, tank, hose); return g;
        }
        function truck() {
            var g = new THREE.Group();
            var cargo = new THREE.Mesh(new THREE.BoxGeometry(0.9, 0.55, 0.55), navy); cargo.position.set(0.15, 0.15, 0);
            var cab = new THREE.Mesh(new THREE.BoxGeometry(0.35, 0.35, 0.55), cyan); cab.position.set(-0.5, 0.15, 0);
            var w1 = new THREE.Mesh(new THREE.CylinderGeometry(0.13, 0.13, 0.08, 14), orange); w1.rotation.z = Math.PI / 2; w1.position.set(-0.5, -0.25, 0.2);
            var w2 = w1.clone(); w2.position.set(-0.5, -0.25, -0.2);
            var w3 = new THREE.Mesh(new THREE.CylinderGeometry(0.13, 0.13, 0.08, 14), orange); w3.rotation.z = Math.PI / 2; w3.position.set(0.35, -0.25, 0.2);
            var w4 = w3.clone(); w4.position.set(0.35, -0.25, -0.2);
            g.add(cargo, cab, w1, w2, w3, w4); return g;
        }
        function electric() {
            var g = new THREE.Group();
            var core = new THREE.Mesh(new THREE.OctahedronGeometry(0.32), orange);
            var boltA = new THREE.Mesh(new THREE.TetrahedronGeometry(0.2), cyan); boltA.position.set(0.5, 0.1, 0);
            var boltB = new THREE.Mesh(new THREE.TetrahedronGeometry(0.16), cyan); boltB.position.set(-0.45, -0.15, 0);
            g.add(core, boltA, boltB); return g;
        }

        var builders = [screwdriver, wrench, broom, vacuum, truck, electric];
        var tools = [];
        for (var i = 0; i < 18; i++) {
            var tool = builders[Math.floor(Math.random() * builders.length)]();
            var radius = 2.1 + Math.random() * 1.9;
            var theta = Math.random() * Math.PI * 2;
            var phi = Math.acos(2 * Math.random() - 1);
            tool.position.set(Math.sin(phi) * Math.cos(theta) * radius, Math.sin(phi) * Math.sin(theta) * radius * 0.8, Math.cos(phi) * radius * 0.9);
            var scale = 0.55 + Math.random() * 0.5;
            tool.scale.setScalar(scale);
            tool.userData = { speed: 0.4 + Math.random() * 0.8, phase: Math.random() * Math.PI * 2, amp: 0.14 + Math.random() * 0.18 };
            scene.add(tool);
            tools.push(tool);
        }

        var pointer = { x: 0, y: 0 };
        var interactive = wrap.closest('.proyetech-hero') || wrap;
        interactive.addEventListener('pointermove', function (event) {
            var rect = interactive.getBoundingClientRect();
            pointer.x = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
            pointer.y = ((event.clientY - rect.top) / rect.height - 0.5) * 2;
        }, { passive: true });

        function resize() {
            camera.aspect = wrap.clientWidth / wrap.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(wrap.clientWidth, wrap.clientHeight);
        }
        window.addEventListener('resize', resize);

        function draw(now) {
            var time = now * 0.001;
            for (var i = 0; i < tools.length; i++) {
                var t = tools[i], d = t.userData;
                t.position.y += Math.sin(time * d.speed + d.phase) * 0.006; // levitación
                t.rotation.x = Math.sin(time * d.speed * 0.6 + d.phase) * 0.35;
                t.rotation.y += 0.004 * d.speed;
                t.rotation.z += 0.002 * d.speed;
            }
            // Parallax suave con el ratón.
            camera.position.x += (pointer.x * 0.9 - camera.position.x) * 0.04;
            camera.position.y += (-pointer.y * 0.7 - camera.position.y) * 0.04;
            camera.lookAt(0, 0, 0);
            renderer.render(scene, camera);
            if (!reducedMotion) window.requestAnimationFrame(draw);
        }
        requestAnimationFrame(draw);
    }());
</script>
<script>
    // Hero dinámico: crossfade de imágenes de profesiones + texto sincronizado.
    (function () {
        var frames = Array.prototype.slice.call(document.querySelectorAll('#proyetech-hero-frames .proyetech-hero-frame'));
        var dotsWrap = document.getElementById('proyetech-hero-dots');
        var professionEl = document.getElementById('hero-profession');
        if (!frames.length) return;

        var professions = ['Arquitecto', 'Médico', 'Desarrollador', 'Electricista'];
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var current = 0;
        var timer = null;

        var dots = [];
        if (dotsWrap) {
            frames.forEach(function (_, index) {
                var dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('role', 'tab');
                dot.setAttribute('aria-label', 'Profesión ' + (professions[index] || index + 1));
                if (index === 0) dot.classList.add('is-active');
                dot.addEventListener('click', function () { show(index); restart(); });
                dotsWrap.appendChild(dot);
                dots.push(dot);
            });
        }

        function show(index) {
            current = (index + frames.length) % frames.length;
            frames.forEach(function (frame, i) { frame.classList.toggle('is-active', i === current); });
            dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === current); });
            if (professionEl) {
                professionEl.classList.add('is-swapping');
                window.setTimeout(function () {
                    professionEl.textContent = professions[current] || professions[0];
                    professionEl.classList.remove('is-swapping');
                }, reducedMotion ? 0 : 260);
            }
        }

        function restart() {
            if (timer) window.clearInterval(timer);
            if (reducedMotion || frames.length < 2) return;
            timer = window.setInterval(function () { show(current + 1); }, 4200);
        }

        restart();
    }());
</script>
<script>
    // Modal independiente por categoría: subcategorías, destacados y filtro rápido.
    (function () {
        var modal = document.getElementById('proyetech-cat-modal');
        if (!modal) return;

        var data = {};
        try { data = JSON.parse(modal.getAttribute('data-category-modals') || '{}') || {}; } catch (e) { data = {}; }

        var titleEl = document.getElementById('proyetech-cat-title');
        var imgEl = document.getElementById('proyetech-cat-img');
        var chipsEl = document.getElementById('proyetech-cat-chips');
        var prosEl = document.getElementById('proyetech-cat-pros');
        var filterEl = document.getElementById('proyetech-cat-filter');
        var exploreEl = document.getElementById('proyetech-cat-explore');
        var dialog = modal.querySelector('.proyetech-cat-dialog');
        var currentServices = [];

        function escapeHtml(value) {
            return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char];
            });
        }

        function renderPros(list) {
            if (!list.length) {
                prosEl.innerHTML = '<p class="proyetech-cat-empty">Aún no hay profesionales publicados en esta categoría.</p>';
                return;
            }
            prosEl.innerHTML = list.map(function (service) {
                var thumb = service.thumbnail ? '/images/services/thumbnails/' + service.thumbnail : '';
                return '<a class="proyetech-cat-pro" href="/service/' + escapeHtml(service.slug) + '">' +
                    (thumb ? '<img class="proyetech-cat-pro-thumb" src="' + escapeHtml(thumb) + '" alt="" loading="lazy">' : '') +
                    '<span class="proyetech-cat-pro-info"><strong>' + escapeHtml(service.name) + '</strong><span>' + escapeHtml(service.tagline || 'Servicio para el hogar') + '</span></span>' +
                    '<span class="proyetech-cat-pro-price">Desde $' + escapeHtml(service.price) + '</span></a>';
            }).join('');
        }

        function openCategory(tile) {
            var slug = tile.getAttribute('data-cat-slug');
            var name = tile.getAttribute('data-cat-name') || 'Categoría';
            var image = tile.getAttribute('data-cat-image') || '';
            var record = data[slug] || { services: [] };
            currentServices = record.services || [];

            titleEl.textContent = name;
            if (imgEl) { imgEl.src = image; imgEl.alt = name; }
            if (exploreEl) exploreEl.href = tile.getAttribute('href');
            if (filterEl) filterEl.value = '';

            chipsEl.innerHTML = currentServices.map(function (service) {
                return '<a class="proyetech-cat-chip" href="/service/' + escapeHtml(service.slug) + '">' + escapeHtml(service.name) + '</a>';
            }).join('') || '<p class="proyetech-cat-empty">Sin subcategorías todavía.</p>';

            renderPros(currentServices);
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('proyetech-locked');
        }

        function closeCategory() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('proyetech-locked');
            if (dialog) dialog.style.maxWidth = '';
        }

        var list = document.querySelector('.proyetech-category-list');
        if (list) {
            list.addEventListener('click', function (event) {
                var tile = event.target.closest('[data-cat-trigger]');
                if (!tile) return;
                event.preventDefault();
                openCategory(tile);
            });
        }

        if (filterEl) {
            filterEl.addEventListener('input', function () {
                var term = this.value.trim().toLowerCase();
                renderPros(currentServices.filter(function (service) {
                    var haystack = ((service.name || '') + ' ' + (service.tagline || '')).toLowerCase();
                    return haystack.indexOf(term) !== -1;
                }));
            });
        }

        var closeBtn = document.getElementById('proyetech-cat-close');
        if (closeBtn) closeBtn.addEventListener('click', closeCategory);
        var fsBtn = document.getElementById('proyetech-cat-fullscreen');
        if (fsBtn) fsBtn.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                if (modal.requestFullscreen) modal.requestFullscreen();
                if (dialog) dialog.style.maxWidth = '100%';
            } else if (document.exitFullscreen) {
                document.exitFullscreen();
                if (dialog) dialog.style.maxWidth = '';
            }
        });
        modal.addEventListener('click', function (event) { if (event.target === modal) closeCategory(); });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeCategory(); });
    }());
</script>
<script>
    // Módulo unificado de autenticación: tab toggle deslizable sin recargar.
    (function () {
        var modal = document.getElementById('proyetech-auth-modal');
        if (!modal) return;
        var tabsWrap = document.getElementById('proyetech-auth-tabs');

        function setMode(mode) {
            modal.setAttribute('data-active', mode);
            if (tabsWrap) tabsWrap.setAttribute('data-active', mode);
            document.querySelectorAll('.proyetech-auth-tab').forEach(function (tab) {
                var isActive = tab.getAttribute('data-auth-tab') === mode;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            setTimeout(function () {
                var firstInput = modal.querySelector('.proyetech-auth-pane[data-auth-pane="' + mode + '"] input:not([type="hidden"])');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function openAuth(mode) {
            setMode(mode || 'login');
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('proyetech-locked');
        }

        function closeAuth() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('proyetech-locked');
        }

        document.querySelectorAll('[data-auth-open]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) { event.preventDefault(); openAuth(this.getAttribute('data-auth-open') || 'login'); });
        });
        document.querySelectorAll('[data-auth-tab]').forEach(function (tab) {
            tab.addEventListener('click', function () { setMode(this.getAttribute('data-auth-tab')); });
        });
        document.querySelectorAll('[data-auth-goto]').forEach(function (link) {
            link.addEventListener('click', function () { setMode(this.getAttribute('data-auth-goto')); });
        });
        var closeBtn = document.getElementById('proyetech-auth-close');
        if (closeBtn) closeBtn.addEventListener('click', closeAuth);
        modal.addEventListener('click', function (event) { if (event.target === modal) closeAuth(); });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeAuth(); });

        window.addEventListener('resize', function () {
            var currentMode = modal.getAttribute('data-active') || 'login';
            adjustHeight(currentMode);
        });

        document.querySelectorAll('[data-social]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'info', title: 'Próximamente', text: 'El acceso con ' + this.getAttribute('data-social') + ' estará disponible pronto.' });
                }
            });
        });

        @if ($errors->any())
            openAuth('{{ old('registeras') ? 'register' : 'login' }}');
        @endif
    }());
</script>
<script>
    var path = "{{ route('autocomplete') }}";
    if (window.jQuery && $.fn.typeahead) { $('input.typeahead').typeahead({ source: function (query, process) { return $.get(path, { query: query }, process); } }); }
</script>
@endpush
