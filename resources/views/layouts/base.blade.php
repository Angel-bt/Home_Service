<!DOCTYPE html>
<html lang="en">
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
                    <li><a href="tel:+911234567890"><i class="fa fa-phone"></i> +593-995528556</a></li>
                    <li><a href="mailto:contact@surfsidemedia.in"><i class="fa fa-envelope"></i> gameri7@hotmail.com</a></li>
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

        <header id="header" class="header-v3 proyetech-nav">
            <nav class="flat-mega-menu" aria-label="Navegación principal">
                <label for="mobile-button" aria-label="Abrir menú de navegación"> <i class="fa fa-bars" aria-hidden="true"></i></label>
                <input id="mobile-button" type="checkbox">

                <ul class="neomorph-card proyetech-nav-list" >
                    <li class="glass-card" >
                        <a href="{{ route('home') }}" aria-label="Ir al inicio"><img src="{{ asset('images/logo.png') }}" alt="PROYETECH"></a>
                    </li>
                    <li> <a href="{{ route('home.service_categories') }}">Categorías</a>
                    </li>
                     
                    <li> <a href="{{ route('home.service_categories') }}"  class="neomorph-card" aria-haspopup="true">Electrodomésticos</a>
                        <ul class="drop-down one-column hover-fade">
                        @foreach (App\Models\ServiceCategory::whereIn('name', ['Computer Repair','TV', 'AC','Gyser', 'Refrigerator', 'Washing Machine','Chimney and Hob', 'Microwave Oven', 'Water Purifier'])->distinct()->get() as $category)
                        <li><a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
                        @endforeach
                        </ul>
                    </li>

                    <li> <a href="{{ route('home.service_categories') }}" class="neomorph-card" aria-haspopup="true">Necesidades del hogar</a>

                    <ul class="drop-down one-column hover-fade">
                        @foreach (App\Models\ServiceCategory::whereIn('name', ['Laundry','Electrical', 'Pest Control','Carpentry', 'Plumbing', 'Painting','Movers & Packers', 'Shower Filters'])->distinct()->get() as $category)
                        <li><a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
                        @endforeach
                        </ul>
                        
                    </li>
                    <li> <a href="{{ route('home.service_categories') }}" class="neomorph-card" aria-haspopup="true">Limpieza</a>
                    <ul class="drop-down one-column hover-fade">
                        @foreach (App\Models\ServiceCategory::whereIn('name', ['Bedroom Deep Cleaning','Overhead Water Storage', 'Tank Cleaning','Underground Sump Cleaning', 'Dining Chair Shampooing', 'Office Chair Shampooing','Home Deep Cleaning', 'Carpet Shampooing', 'Fabric Sofa Shampooing','Bathroom Deep Cleaning','Floor Scrubbing & Polishing','Mattress Shampooing','Kitchen Deep Cleaning'])->distinct()->get() as $category)
                        <li><a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
                        @endforeach
                        </ul>

                    </li>
                    <li> <a href="{{ route('home.service_categories') }}" class="neomorph-card" aria-haspopup="true">Servicios especiales</a>
                        <ul class="drop-down one-column hover-fade">
                            <li><a href="{{ route('home.service_categories') }}">Servicios de documentos</a></li>
                            <li><a href="{{ route('home.service_categories') }}">Coches y bicicletas</a></li>
                            <li><a href="{{ route('home.service_categories') }}">Mudanzas y embalajes</a></li>
                            <li><a href="{{ route('home.service_categories') }}">Automatización del hogar</a></li>
                        </ul>
                    </li>
                    @if (Route::has('login'))
                        @auth
                            @if (Auth::user()->utype==='ADM')
                                <li class="login-form"><a href="#" class="neomorph-card" title="My Account (Admin)">Welcome, {{ auth()->user()->name }}!(Admin)</a>
                                    <ul class="drop-down one-column hover-fade">
                                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                        <li><a href="{{ route('admin.service_categories') }}">Service Categories</a></li>
                                        <li><a href="{{ route('admin.all_services') }}">All Services</a></li>
                                        <li><a href="{{ route('admin.slider') }}">Manage Slider</a></li>
                                        <li><a href="{{ route('admin.contacts') }}">All Contacts</a></li>
                                        <li><a href="{{ route('admin.service_providers') }}">All Service Providers</a></li>
                                        <li><a  href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                                    </ul>
                                </li>
                            
                            @elseif(Auth::user()->utype==='SVP')
                            <li class="login-form"><a href="#" title="My Account (S Providers)">Welcome, {{ auth()->user()->name }}!(S Provd)</a>
                                <ul class="drop-down one-column hover-fade">
                                    <li><a href="{{ route('sprovider.dashboard') }}">Dashboard</a></li>
                                    <li><a href="{{ route('sprovider.profile') }}">My Profile</a></li>
                                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                                </ul>
                            </li>
                            @else
                            <li class="login-form"><a href="#" title="My Account (Customer)">Welcome, {{ auth()->user()->name }}!(Customer)</a>
                                <ul class="drop-down one-column hover-fade">
                                    <li><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                                    <li><a  href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                                </ul>
                            </li>
                            @endif
                        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none">
                            @csrf
                        </form>
                        @else
                            {{-- MEJORA: botón único que abre el módulo unificado de Login + Registro. --}}
                            <li class="login-form">
                                <button type="button" class="proyetech-auth-trigger" data-auth-open="login" title="Acceder o crear cuenta">
                                    <i class="fa fa-user-circle" aria-hidden="true"></i><span>Acceder / Crear cuenta</span>
                                </button>
                            </li>
                        @endif
                    @endif

                    <li class="search-bar">
                        
                    </li>
                </ul>
            </nav>
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
                    <div class="col-md-3 col-xs-6 col-sm-6">
                        
                    <h3>SERVICIOS DE ELECTRODOMÉSTICOS </h3>
                    <ul>
            @foreach (App\Models\ServiceCategory::whereIn('name', ['Computer Repair','TV', 'AC','Gyser', 'Refrigerator', 'Washing Machine','Chimney and Hob', 'Microwave Oven', 'Water Purifier'])->distinct()->get() as $category)
                <li><i class="fa fa-check"></i> <a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
            @endforeach
        </ul>
                    </div>
                    <div class="col-md-3 col-xs-6 col-sm-6">
                        <h3>SERVICIOS DE CLIMATIZACIÓN </h3>
                        <ul>
            @foreach (App\Models\ServiceCategory::whereIn('name', ['Installation','Uninstallation', 'AC Repair','Gas Refill', 'Wet Servicing', 'Dry Servicing'])->distinct()->get() as $category)
                <li><i class="fa fa-check"></i> <a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
            @endforeach
        </ul>
                    </div>
                    <div class="col-md-3 col-xs-6 col-sm-6">
                        <h3>NECESIDADES DEL HOGAR </h3>
                        <ul>
            @foreach (App\Models\ServiceCategory::whereIn('name', ['Laundry','Electrical', 'Pest Control','Carpentry', 'Plumbing', 'Painting','Movers & Packers', 'Shower Filters'])->distinct()->get() as $category)
                <li><i class="fa fa-check"></i> <a href="{{ route('home.services_by_category', ['category_slug' => $category->slug]) }}">{{ $category->name }}</a></li>
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
                                <i class="fa fa-envelope"></i> <a
                                    href="mailto:contact@surfsidemedia.in">contact@surfsidemedia.in</a>
                            </li>
                            <li>
                                <i class="fa fa-headphones"></i> <a href="tel:+911234567890">+91-1234567890</a>
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
                                <i class="fa fa-envelope"></i> <a
                                    href="mailto:contact@surfsidemedia.in">contact@surfsidemedia.in</a>
                            </li>
                            <li>
                                <i class="fa fa-phone"></i> <a href="tel:+911234567890">+91-1234567890</a>
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
                                <li><a href="{{ route('home.privacy') }}">Privacy</a></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <p class="text-xs-center crtext">&copy; 2025 Proyetech. Todos los derechos reservados.</p>
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
 <!-- Scripts de SweetAlert2 -->
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mensajes de éxito y error -->
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
    });
</script>
@endif

@if ($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ $errors->first() }}',
    });
</script>
@endif

<!-- Cierre del body -->

</body>
</html>
