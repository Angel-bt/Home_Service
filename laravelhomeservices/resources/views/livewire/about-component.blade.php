<div>
    @include('livewire.partials.page-header', ['title' => 'Nosotros'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1" style="font-size: 16px; line-height: 1.7;">
                            <h2>Servicios de confianza para tu hogar y tu quinta</h2>
                            <p>
                                Proyetech conecta a familias, dueños de quintas y negocios de Malacatos y del valle
                                (Vilcabamba, Quinara, San Pedro de Vilcabamba, Yangana, Catamayo y Loja) con proveedores
                                locales de servicios: gasfitería, electricidad, jardinería, piscinas, albañilería,
                                limpieza y mucho más.
                            </p>
                            <h3>Cómo te ayudamos</h3>
                            <ul class="list-styles">
                                <li><i class="fa fa-check"></i> Encuentras proveedores que trabajan en tu zona.</li>
                                <li><i class="fa fa-check"></i> Les escribes directamente por WhatsApp o les envías una solicitud.</li>
                                <li><i class="fa fa-check"></i> Ves las reseñas de clientes que ya contrataron el servicio.</li>
                                <li><i class="fa fa-check"></i> Acuerdas el precio y pagas directamente al proveedor.</li>
                            </ul>
                            <h3>¿Eres proveedor?</h3>
                            <p>
                                Regístrate como proveedor, completa tu perfil con tu WhatsApp y las zonas donde trabajas,
                                y publica tus servicios. El plan gratuito te permite publicar hasta
                                {{ config('marketplace.free_service_limit') }} servicios.
                            </p>
                            <p>
                                <a href="{{ route('register') }}" class="btn btn-primary">Crear cuenta</a>
                                <a href="{{ route('home.contact') }}" class="btn btn-default">Contáctanos</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
