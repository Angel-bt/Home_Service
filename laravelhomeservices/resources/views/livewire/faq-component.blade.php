<div>
    @include('livewire.partials.page-header', ['title' => 'Preguntas frecuentes'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1" style="font-size: 16px; line-height: 1.7;">
                            @php
                                $faqs = [
                                    '¿Cuánto cuesta usar Proyetech?' => 'Para los clientes es gratis. Buscas, contactas y solicitas servicios sin pagar nada a la plataforma.',
                                    '¿Cómo pago el servicio?' => 'Pagas directamente al proveedor (efectivo, transferencia o DeUna), según lo que acuerden. El precio publicado es referencial; el final depende del trabajo.',
                                    '¿Cómo contacto a un proveedor?' => 'En cada servicio tienes un botón de WhatsApp para escribirle de inmediato y un formulario para enviar una solicitud con tu zona, dirección y fecha preferida.',
                                    '¿En qué zonas funciona?' => 'Malacatos y el valle: ' . implode(', ', config('marketplace.zones')) . '. Puedes filtrar los servicios por zona.',
                                    '¿Quién puede dejar reseñas?' => 'Solo los clientes cuya solicitud fue marcada como completada por el proveedor. Así las reseñas reflejan trabajos reales.',
                                    '¿Cómo publico mis servicios?' => 'Crea una cuenta como "Proveedor de servicios", completa tu perfil (WhatsApp y zonas) y entra a "Mis servicios". El plan gratuito permite ' . config('marketplace.free_service_limit') . ' servicios.',
                                    '¿Qué hago si tengo un problema con un proveedor?' => 'Escríbenos desde la página de contacto con los detalles de tu solicitud y lo revisamos.',
                                ];
                            @endphp
                            @foreach ($faqs as $question => $answer)
                                <h4 style="margin-top: 25px;"><i class="fa fa-question-circle text-primary"></i> {{ $question }}</h4>
                                <p>{{ $answer }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
