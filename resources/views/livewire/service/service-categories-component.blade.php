<div>
    {{-- MEJORA UI: hero editorial y categorías navegables conservando rutas existentes. --}}
    <div class="section-title-01 honmob proyetech-category-hero">
        <div class="bg_parallax image_01_parallax"></div>
        <div class="opacy_bg_02">
            <div class="container">
                <p class="proyetech-eyebrow">PROYETECH / MARKETPLACE</p>
                <h1>Service Categories</h1>
                <div class="crumbs proyetech-breadcrumb" aria-label="Breadcrumb">
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li aria-hidden="true">/</li>
                        <li aria-current="page">Service Categories</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <section class="content-central proyetech-categories-section" aria-labelledby="categories-title">
        <div class="container">
            <div class="row" style="margin-top: -30px;">
                <div class="titles">
                    <h2 id="categories-title">Find the right <span>specialist</span></h2>
                    <p class="proyetech-section-copy">Explore trusted home services and choose the category that fits your need.</p>
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
                                        <span class="proyetech-category-description">Encuentra profesionales verificados para tu hogar.</span>
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
