<div>
    <div class="section-title-01 honmob">
        <div class="bg_parallax image_02_parallax"></div>
        <div class="opacy_bg_02">
            <div class="container">
                <h1>Contáctanos</h1>
                <div class="crumbs">
                    <ul>
                    <li><a href="/"><button class="glass-button">Inicio</button></a></li>
                        <li>/</li>
                        <li>Contáctanos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="content-central">
        <div class="semiboxshadow text-center">
            <img src="img/img-theme/shp.png" class="img-responsive" alt="">
        </div>
        <div id="map" class="honmob"> </div>
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <aside>
                                <h4>Nuestra oficina</h4>
                                <address>
                                    <strong>Proyetech · Servicios para el hogar</strong><br>
                                    <i class="fa fa-map-marker"></i> <strong>Dirección:</strong> Malacatos, Loja, Ecuador<br>
                                    <i class="fa fa-phone"></i> <strong>Teléfono:</strong> <a href="tel:{{ config('marketplace.contact_phone') }}">{{ config('marketplace.contact_phone') }}</a><br>
                                    <i class="fab fa-whatsapp"></i> <strong>WhatsApp:</strong> <a href="{{ \App\Support\WhatsApp::link(config('marketplace.contact_phone')) }}" target="_blank" rel="noopener">Escríbenos</a>
                                </address>
                                <address>
                                    <i class="fa fa-envelope"></i> <strong>Correo:</strong> <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>
                                </address>
                            </aside>
                            <hr class="tall">
                        </div>
                        <div class="col-md-8">
                            <h3>Escríbenos</h3>
                            <p class="lead">
                            </p>
                            @if (Session::has('message'))
                                <div class="alert alert-success" role="alert">{{ Session::get('message') }}</div>
                            @endif
                            <form id="contactform" class="form-theme" wire:submit.prevent='sendMessage'>
                                <input type="text" placeholder="Nombre" name="name" id="name" required=""wire:model="name">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <input type="email" placeholder="Correo" name="email" id="email" required=""wire:model="email">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <input type="text" placeholder="Teléfono" name="phone" id="phone" required=""wire:model="phone">
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <textarea placeholder="Tu mensaje" name="message" id="message" required=""wire:model="message"></textarea>
                                @error('message')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                <input type="submit" name="Submit" value="Enviar mensaje" class="btn btn-primary">
                            </form>
                            <div id="result"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-twitter content_resalt border-top">
            <i class="fa fa-twitter icon-big"></i>
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-center">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
