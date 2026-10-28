<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Servicios para tu hogar | Proyetech</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('assets/css/chblue.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('assets/css/theme-responsive.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('assets/css/dtb/jquery.dataTables.min.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" media="screen">
    <link href="{{ asset('assets/css/toastr.min.css') }}" rel="stylesheet" media="screen">
    {{-- MEJORA UI: estilos premium aislados para no alterar los assets heredados. --}}
    <link href="{{ asset('css/proyetech.css') }}" rel="stylesheet">
    {{-- Utilidades Tailwind usadas por el encabezado y la portada (npm run dev / prod). --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <script type="text/javascript" src="{{ asset('assets/js/jquery.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/jquery-ui.1.10.4.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/toastr.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modernizr.js') }}"></script>
    <!-- AOS CSS -->
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">


<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    @livewireStyles
    


</head>
<body>
    <div id="layout">
    <div class="info-head proyetech-topbar">
    <div class="container">
        <div class="row d-flex align-items-center justify-content-between text-center">
            <!-- Columna de información -->
            <div class="col-md-6 d-flex flex-column flex-md-row justify-content-between">
                <ul class="d-none d-md-block text-left proyetech-contact-list">
                    <li><a href="tel:{{ config('marketplace.contact_phone') }}"><i class="fa fa-phone"></i> {{ config('marketplace.contact_phone') }}</a></li>
                    <li><a href="mailto:{{ config('marketplace.contact_email') }}"><i class="fa fa-envelope"></i> {{ config('marketplace.contact_email') }}</a></li>
                </ul>

                
            </div>

            <div class="row">
    <!-- Componente de ubicación -->
    <div class="col-md-3 d-flex justify-content-right proyetech-location">
        @livewire('location-component')
    </div>

    <!-- Contador de visitas alineado en la misma fila -->
    <div class="col-md-3 d-flex justify-content-right proyetech-visits">
        @livewire('visit-counter')
    </div>
</div>
        </div>
    </div>
</div>

        <header class="sticky top-0 z-50 w-full backdrop-blur-md bg-white/80 border-b border-gray-100 shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 transition-transform hover:scale-105">
                            <img src="{{ asset('images/logo.png') }}" alt="PROYETECH" class="h-10 w-auto">
                        </a>
                    </div>

                    <!-- Desktop Menu -->
                    <nav class="hidden md:flex space-x-8 items-center" aria-label="Navegación principal">
                        
                        <!-- Categorías Dropdown -->
                        <div class="relative group">
                            <a href="{{ route('home.service_categories') }}" class="text-gray-700 hover:text-blue-600 font-medium inline-flex items-center px-1 pt-1 border-b-2 border-transparent hover:border-blue-600 transition-colors duration-200">
                                Explorar Servicios
                                <i class="fa fa-chevron-down ml-1 text-xs"></i>
                            </a>
                            <!-- Dropdown Menu -->
                            <div class="absolute left-0 mt-2 w-64 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-left -translate-y-2 group-hover:translate-y-0 z-50">
                                <div class="rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden bg-white">
                                    <div class="p-4">
                                        <ul class="space-y-1">
                                            @foreach (App\Models\ServiceCategory::query()->orderByDesc('featured')->orderBy('name')->take(10)->get(['name', 'slug']) as $category)
                                            <li><a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}" class="block px-2 py-1 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-md transition-colors">{{ $category->name }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 text-center">
                                        <a href="{{ route('home.service_categories') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Ver todas las categorías &rarr;</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enlaces adicionales -->
                        <a href="{{ route('search') }}" class="text-gray-700 hover:text-blue-600 font-medium px-1 pt-1 border-b-2 border-transparent transition-colors duration-200"><i class="fa fa-search"></i> Buscar</a>
                        <a href="{{ route('home.about') }}" class="text-gray-700 hover:text-blue-600 font-medium px-1 pt-1 border-b-2 border-transparent transition-colors duration-200">Nosotros</a>
                        <a href="{{ route('home.contact') }}" class="text-gray-700 hover:text-blue-600 font-medium px-1 pt-1 border-b-2 border-transparent transition-colors duration-200">Contacto</a>
                    </nav>

                    <!-- Auth Section -->
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <div class="relative group">
                                    <button type="button" class="flex items-center gap-2 text-gray-700 hover:text-blue-600 font-medium focus:outline-none">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shadow-sm">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                        <span class="hidden md:block">Hola, {{ explode(' ', auth()->user()->name)[0] }}</span>
                                        <i class="fa fa-chevron-down text-xs"></i>
                                    </button>
                                    
                                    <div class="absolute right-0 mt-2 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform origin-top-right -translate-y-2 group-hover:translate-y-0 z-50">
                                        <div class="rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 overflow-hidden bg-white py-1">
                                            @if (Auth::user()->utype==='ADM')
                                                <div class="px-4 py-2 border-b border-gray-100"><span class="block text-xs font-bold text-gray-400">ADMINISTRADOR</span></div>
                                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">Dashboard</a>
                                                <a href="{{ route('admin.service_categories') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Categorías</a>
                                                <a href="{{ route('admin.all_services') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Servicios</a>
                                                <a href="{{ route('admin.service_requests') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Solicitudes</a>
                                                <a href="{{ route('admin.service_providers') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Proveedores</a>
                                                <a href="{{ route('admin.payments') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Pagos Pro</a>
                                            @elseif(Auth::user()->utype==='SVP')
                                                <div class="px-4 py-2 border-b border-gray-100"><span class="block text-xs font-bold text-gray-400">PROVEEDOR</span></div>
                                                <a href="{{ route('sprovider.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">Mi Panel</a>
                                                <a href="{{ route('sprovider.services') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Mis servicios</a>
                                                <a href="{{ route('sprovider.requests') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Solicitudes</a>
                                                <a href="{{ route('sprovider.plan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Mi plan</a>
                                                <a href="{{ route('sprovider.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Mi perfil</a>
                                            @else
                                                <div class="px-4 py-2 border-b border-gray-100"><span class="block text-xs font-bold text-gray-400">CLIENTE</span></div>
                                                <a href="{{ route('customer.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">Mis solicitudes</a>
                                            @endif
                                            
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50">Mi cuenta y seguridad</a>
                                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Cerrar Sesión</a>
                                        </div>
                                    </div>
                                </div>
                                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none">@csrf</form>
                            @else
                                <button type="button" class="proyetech-auth-trigger group relative inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-white transition-all duration-200 bg-green-700 border border-transparent rounded-full hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600 shadow-md shadow-green-500/30 hover:shadow-lg hover:shadow-green-500/40" data-auth-open="login" title="Acceder o crear cuenta">
                                    <i class="fa fa-user-circle mr-2 group-hover:scale-110 transition-transform"></i>
                                    <span>Acceder / Registro</span>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </header>
          <!-- Mostrar el nombre del usuario autenticado 
           
          @auth
            <div class="user-info text-center">
                <p>Welcome, {{ auth()->user()->name }}!</p>
            </div>
        @endauth
          -->
          
        {{ $slot }}

        
        
        <footer id="footer" class="footer-v1">
            <div class="container">
                <div class="row visible-md visible-lg">
                    <div class="col-md-6 col-xs-12 col-sm-6">
                        <h3>CATEGORÍAS</h3>
                        <ul>
                            @foreach (App\Models\ServiceCategory::query()->orderBy('name')->take(12)->get(['name', 'slug']) as $category)
                                <li><i class="fa fa-check"></i> <a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-3 col-xs-6 col-sm-6">
                        <h3>ZONAS</h3>
                        <ul>
                            @foreach (config('marketplace.zones') as $slug => $label)
                                <li><i class="fa fa-map-marker"></i> <a href="{{ route('search', ['zona' => $slug]) }}">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="col-md-3 col-xs-6 col-sm-6">
                        <h3>CONTÁCTANOS</h3>
                        <ul class="contact_footer">
                        <li class="location">
    <i class="fa fa-map-marker"></i> 
    <a href="https://www.google.com/maps?q=Loja,+Malacatos" target="_blank">Loja, Malacatos</a>
</li>

                            <li>
                                <i class="fa fa-envelope"></i> <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>
                            </li>
                            <li>
                                <i class="fab fa-whatsapp"></i> <a href="{{ \App\Support\WhatsApp::link(config('marketplace.contact_phone')) }}" target="_blank" rel="noopener">{{ config('marketplace.contact_phone') }}</a>
                            </li>
                        </ul>
                        <h3 style="margin-top: 10px">SÍGUENOS</h3>
                        <ul class="social">
                            <li class="facebook"><span><i class="fa fa-facebook"></i></span><a href="#"></a></li>
                            <li class="twitter"><span><i class="fa fa-twitter"></i></span><a href="#"></a></li>
                            <li class="github"><span><i class="fa fa-instagram"></i></span><a href="#"></a></li>
                        </ul>
                    </div>
                </div>
                <div class="row visible-sm visible-xs">
                    <div class="col-md-6">
                        <h3 class="mlist-h">CONTÁCTANOS</h3>
                        <ul class="contact_footer mlist">
                           
                            <li>
                                <i class="fa fa-envelope"></i> <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>
                            </li>
                            <li>
                                <i class="fab fa-whatsapp"></i> <a href="{{ \App\Support\WhatsApp::link(config('marketplace.contact_phone')) }}" target="_blank" rel="noopener">{{ config('marketplace.contact_phone') }}</a>
                            </li>
                        </ul>
                        <ul class="social mlist-h">
                            <li class="facebook"><span><i class="fa fa-facebook"></i></span><a href="#"></a></li>
                            <li class="twitter"><span><i class="fa fa-twitter"></i></span><a href="#"></a></li>
                            <li class="github"><span><i class="fa fa-instagram"></i></span><a href="#"></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="footer-down">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="nav-footer">
                                <li><a href="{{ route('home.about') }}">Nosotros</a> </li>
                                <li><a href="{{ route('home.contact') }}">Contacto</a></li>
                                <li><a href="{{ route('home.faq') }}">FAQ</a></li>
                                <li><a href="{{ route('home.terms') }}">Términos de uso</a></li>
                                <li><a href="{{ route('home.privacy') }}">Privacidad</a></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <p class="text-xs-center crtext">&copy; {{ date('Y') }} Proyetech. Todos los derechos reservados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <script type="text/javascript" src="{{ asset('assets/js/nav/jquery.sticky.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/totop/jquery.ui.totop.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/accordion/accordion.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/rs-plugin/js/jquery.themepunch.tools.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/rs-plugin/js/jquery.themepunch.revolution.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/maps/gmap3.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/fancybox/jquery.fancybox.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/carousel/carousel.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/filters/jquery.isotope.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/twitter/jquery.tweet.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/flickr/jflickrfeed.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/theme-options/theme-options.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/theme-options/jquery.cookies.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/bootstrap/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/bootstrap/bootstrap-slider.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/dtb/jquery.dataTables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/dtb/jquery.table2excel.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/dtb/script.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/validation-rule.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/bootstrap3-typeahead.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/main.js') }}"></script>
    <script type="text/javascript">
        
        jQuery(document).ready(function () {
            jQuery('.tp-banner').show().revolution({
                dottedOverlay: "none",
                delay: 5000,
                startwidth: 1170,
                startheight: 480,
                minHeight: 250,
                navigationType: "none",
                navigationArrows: "solo",
                navigationStyle: "preview1"
            });
        });
    </script>
    
    @stack('modals')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts') <!-- Asegura que las vistas puedan agregar scripts adicionales -->
    {{-- MEJORA: fallback del botón único de acceso cuando el modal no está en la página. --}}
    <script>
        (function () {
            var trigger = document.querySelector('.proyetech-auth-trigger[data-auth-open]');
            if (!trigger) return;
            trigger.addEventListener('click', function () {
                if (!document.getElementById('proyetech-auth-modal')) {
                    window.location.href = "{{ route('login') }}";
                }
            });
        }());
    </script>
    {{-- MEJORA: estado activo en la navegación principal. --}}
    <script>
        (function () {
            var links = document.querySelectorAll('.proyetech-nav-list > li > a');
            for (var i = 0; i < links.length; i++) {
                links[i].addEventListener('click', function () {
                    for (var j = 0; j < links.length; j++) links[j].classList.remove('active');
                    this.classList.add('active');
                });
            }
        }());
    </script>

<!-- Mensajes de éxito y error -->
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Listo',
        text: @json(session('success')),
    });
</script>
@endif

@if (isset($errors) && $errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: @json($errors->first()),
    });
</script>
@endif

<!-- Cierre del body -->

</body>
</html>
