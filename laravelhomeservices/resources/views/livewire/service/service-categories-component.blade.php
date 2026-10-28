<div>
    {{-- MEJORA UI: hero editorial y categorías navegables conservando rutas existentes. --}}
    <div class="section-title-01 honmob proyetech-category-hero">
        <div class="bg_parallax image_01_parallax"></div>
        <div class="opacy_bg_02">
            <div class="container">
                <p class="proyetech-eyebrow">PROYETECH · MALACATOS</p>
                <h1>Categorías de servicios</h1>
                <div class="crumbs proyetech-breadcrumb" aria-label="Breadcrumb">
                    <ul>
                        <li><a href="{{ route('home') }}">Inicio</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page">Categorías de servicios</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <section class="content-central proyetech-categories-section" aria-labelledby="categories-title">
        <div class="container">
            <div class="row" style="margin-top: -30px;">
                <div class="titles">
                    <h2 id="categories-title">Encuentra al <span>especialista</span></h2>
                    <p class="proyetech-section-copy">Explora servicios para tu hogar o quinta y elige la categoría que necesitas.</p>
                    <hr class="tall">
                </div>
            </div>
        </div>
        <div class="content_info proyetech-category-grid-wrap">
            <div class="row">
                <div class="col-md-12">
                    <ul class="services-lines full-services proyetech-category-grid">

                        @foreach ($scategories as $scategory)
                            <li>
                                <a class="item-service-line proyetech-category-card" href="{{ route('home.services_by_category', ['category_slug' => $scategory->slug]) }}" aria-label="Ver servicios de {{ $scategory->name }}">
                                    <span class="proyetech-category-icon" aria-hidden="true"><img class="icon-img"
                                        src="{{ asset('images/categories') }}/{{ $scategory->image }}" alt=""></span>
                                    <span class="proyetech-category-content">
                                        <span class="proyetech-category-title">{{ $scategory->name }}</span>
                                        <span class="proyetech-category-description">
                                            {{ $scategory->services->count() }} {{ Str::plural('servicio', $scategory->services->count()) }} disponible{{ $scategory->services->count() !== 1 ? 's' : '' }}
                                        </span>
                                    </span>
                                    <i class="fa fa-arrow-right proyetech-category-arrow" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
        <div class="content_info content_resalt">
            <div class="container">
                <div class="row">
                    <div class="titles">
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
