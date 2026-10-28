<div class="proyetech-home bg-white font-sans">
    <!-- Compact Hero Section (TaskRabbit Style) -->
    <section class="relative w-full h-[400px] md:h-[500px] overflow-hidden group bg-gray-900">
        <!-- Static High-Quality Background (Or Carousel) -->
        <div id="hero-carousel" class="w-full h-full relative">
            <div class="carousel-slide absolute inset-0 w-full h-full opacity-100 z-10 transition-opacity duration-1000">
                <img src="https://images.unsplash.com/photo-1581578731548-c64695cc6952?q=80&w=2070&auto=format&fit=crop" class="w-full h-full object-cover opacity-80" alt="Limpieza profesional">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
            <!-- Slides 2 and 3 omitted for cleaner static look, but script keeps them rotating -->
            <div class="carousel-slide absolute inset-0 w-full h-full opacity-0 z-0 transition-opacity duration-1000">
                <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?q=80&w=2069&auto=format&fit=crop" class="w-full h-full object-cover opacity-80" alt="Electricista">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
            <div class="carousel-slide absolute inset-0 w-full h-full opacity-0 z-0 transition-opacity duration-1000">
                <img src="https://images.unsplash.com/photo-1542013936693-884638332954?q=80&w=1974&auto=format&fit=crop" class="w-full h-full object-cover opacity-80" alt="Plomería">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
        </div>

        <!-- Hero Content & Search Bar -->
        <div class="absolute inset-0 z-20 flex flex-col items-center justify-center px-4 w-full">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-8 drop-shadow-md text-center max-w-2xl">
                Servicios de confianza <br/>para tu hogar y tu quinta
            </h1>
            
            <!-- Large Floating Search Bar -->
            <div class="w-full max-w-3xl bg-white rounded-md shadow-2xl p-2 flex items-center">
                <form id="sform" class="w-full flex items-center" action="{{ route('search') }}" method="get">
                    <div class="flex-grow flex items-center pl-4">
                        <i class="fa fa-search text-green-700 text-lg"></i>
                        <input type="text" id="q" name="q" required placeholder="¿Qué necesitas? Ej.: plomero, jardinería, piscina" class="w-full border-none focus:ring-0 text-gray-700 text-lg py-3 px-4 outline-none typeahead" autocomplete="off">
                    </div>
                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-bold py-3 px-8 rounded cursor-pointer transition-colors whitespace-nowrap">
                        Buscar
                    </button>
                </form>
            </div>
            
            <!-- Rotating Profession Text -->
            <div class="mt-6 flex gap-3 text-white font-medium text-sm md:text-base drop-shadow-md">
                <span class="flex items-center gap-1"><i class="fa fa-check text-green-400"></i> Proveedores de Malacatos y el valle</span>
                <span class="flex items-center gap-1"><i class="fa fa-check text-green-400"></i> Contacto directo por WhatsApp</span>
            </div>
        </div>
    </section>

    <!-- Popular Projects (Featured Services) -->
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-12" aria-labelledby="popular-projects">
        <h2 id="popular-projects" class="text-2xl font-bold text-gray-900 mb-6">Servicios destacados</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($fservices->take(4) as $service)
                <a href="{{ route('home.service_details', ['service_slug' => $service->slug]) }}" class="group block border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow bg-white flex flex-col h-full">
                    <div class="w-full h-40 overflow-hidden relative">
                        <img src="{{ asset('images/services/thumbnails') }}/{{ $service->thumbnail }}" alt="{{ $service->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <span class="absolute top-2 left-2 bg-white/90 text-gray-800 text-xs font-bold px-2 py-1 rounded">DESTACADO</span>
                    </div>
                    <div class="p-4 flex-grow flex flex-col">
                        <h3 class="font-bold text-gray-900 text-lg mb-1 leading-tight group-hover:text-green-700">{{ $service->name }}</h3>
                        <p class="text-gray-500 text-sm mb-3 flex-grow">{{ $service->category->name ?? 'Servicio' }}</p>
                        <div class="text-gray-900 font-semibold text-lg border-t border-gray-100 pt-3">
                            Desde ${{ number_format((float) $service->price, 2) }}
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-gray-500">Pronto habrá más servicios destacados.</p>
            @endforelse
        </div>
    </section>

    <!-- Everyday Help (Categories) -->
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-12 bg-gray-50 rounded-2xl mb-12" aria-labelledby="everyday-help">
        <div class="flex justify-between items-end mb-8">
            <h2 id="everyday-help" class="text-2xl font-bold text-gray-900">Ayuda para el día a día</h2>
            <a href="{{ route('home.service_categories') }}" class="text-green-700 font-bold hover:underline">Ver todas</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($scategories->take(6) as $scategory)
                <a href="{{ route('home.services_by_category', ['category_slug' => $scategory->slug]) }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg hover:border-green-600 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 flex-shrink-0 mr-4 rounded-full bg-green-50 flex items-center justify-center overflow-hidden p-2 group-hover:bg-green-100 transition-colors">
                        <img src="{{ asset('images/categories') }}/{{ $scategory->image }}" alt="{{ $scategory->name }}" class="w-full h-full object-contain mix-blend-multiply">
                    </div>
                    <div class="flex-grow">
                        <h3 class="font-bold text-gray-900 group-hover:text-green-700 transition-colors">{{ $scategory->name }}</h3>
                    </div>
                    <i class="fa fa-chevron-right text-gray-400 group-hover:text-green-700 text-sm"></i>
                </a>
            @empty
                <p class="text-gray-500">Pronto habrá categorías.</p>
            @endforelse
        </div>
    </section>

    <!-- How it works & Metrics -->
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-16 border-t border-gray-200 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-12">Cómo funciona</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center text-green-700 text-2xl font-bold mb-4">1</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Busca lo que necesitas</h3>
                <p class="text-gray-600">Elige la categoría o escribe el trabajo: plomería, jardinería, piscinas y más.</p>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center text-green-700 text-2xl font-bold mb-4">2</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Elige a tu proveedor</h3>
                <p class="text-gray-600">Compara proveedores de tu zona por reseñas y precio.</p>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center text-green-700 text-2xl font-bold mb-4">3</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Contáctalo y listo</h3>
                <p class="text-gray-600">Escríbele por WhatsApp o envía tu solicitud. Pagas directamente al proveedor cuando termina.</p>
            </div>
        </div>
    </section>



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
</div>

@push('scripts')
{{-- MEJORA: estilos del placeholder CSS del hero (el layout solo expone @stack('scripts')). --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const slides = document.querySelectorAll('.carousel-slide');
        if (!slides.length) return;
        
        let currentSlide = 0;
        let slideInterval;
        
        const professions = ['hogar', 'proyecto', 'negocio'];
        const profText = document.getElementById('hero-profession-text');
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.classList.replace('opacity-0', 'opacity-100');
                    slide.classList.replace('z-0', 'z-10');
                } else {
                    slide.classList.replace('opacity-100', 'opacity-0');
                    slide.classList.replace('z-10', 'z-0');
                }
            });
            if (profText) profText.innerText = professions[index % professions.length];
        }
        
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }
        
        function prevSlide() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        }
        
        function startCarousel() {
            slideInterval = setInterval(nextSlide, 5000);
        }
        
        function stopCarousel() {
            clearInterval(slideInterval);
        }
        
        document.getElementById('hero-next')?.addEventListener('click', () => {
            stopCarousel();
            nextSlide();
            startCarousel();
        });
        
        document.getElementById('hero-prev')?.addEventListener('click', () => {
            stopCarousel();
            prevSlide();
            startCarousel();
        });
        
        startCarousel();
    });
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
                tab.classList.toggle('is-active', tab.getAttribute('data-auth-tab') === mode);
            });
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
