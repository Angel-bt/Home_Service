<div>
    @php
        $faqs = [
            'Para clientes' => [
                '¿Cuánto cuesta usar Malakplace?' => 'Para los clientes es gratis. Buscas, contactas y solicitas servicios sin pagar nada a la plataforma.',
                '¿Cómo contacto a un proveedor?' => 'Entra al servicio que te interesa. Ahí tienes un botón verde de WhatsApp para escribirle de inmediato y un formulario para enviarle una solicitud con tu zona, dirección y fecha.',
                '¿Cómo pago el servicio?' => 'Le pagas directamente al proveedor (efectivo o transferencia), según lo que acuerden. El precio publicado es de referencia; el final depende del trabajo.',
                '¿Dónde veo mis solicitudes?' => 'Ingresa con tu cuenta y toca «Mis solicitudes» arriba a la derecha. Ahí ves si el proveedor ya aceptó, puedes cancelar y, al terminar, calificar.',
                '¿Malakplace responde si algo sale mal?' => 'Malakplace solo conecta a las personas: no hace los trabajos, no contrata a los proveedores y no recibe el pago, así que no responde por robos, daños, trabajos mal hechos ni si alguien no llega. Si pasa algo grave, avísanos para revisar esa cuenta y acude a las autoridades.',
                '¿Qué significan los sellos?' => 'Son voluntarios. «Identidad verificada» y «Sin antecedentes penales» dicen que revisamos ese documento en la fecha que aparece. Ayudan a decidir, pero no son una garantía. Que alguien no tenga un sello no significa nada malo.',
                '¿Quién puede dejar reseñas?' => 'Solo los clientes cuya solicitud el proveedor marcó como terminada. Así las reseñas son de trabajos reales.',
            ],
            'Para proveedores' => [
                '¿Cómo publico mis servicios?' => 'Crea una cuenta eligiendo «Ofrezco servicios». En tu panel verás los pasos: completar tu perfil (WhatsApp y zonas) y publicar tu primer servicio. El plan gratuito permite ' . config('marketplace.free_service_limit') . ' servicios.',
                '¿Cómo me llegan los clientes?' => 'Te escriben a tu WhatsApp o te envían una solicitud. Las solicitudes aparecen en tu panel, en «Solicitudes», con un número que te avisa cuántas faltan por responder.',
                '¿Tengo que entregar mis documentos?' => 'No. Es voluntario: puedes publicar y trabajar sin sellos. Si los entregas, solo los ve la administración, se borran apenas los revisamos y en tu perfil solo aparece el sello con su fecha. Puedes retirarlo cuando quieras desde «Verificarme».',
                '¿Qué es el plan Pro?' => 'Te permite publicar servicios sin límite. Se paga por transferencia o en efectivo desde «Mi plan», y lo activamos cuando confirmamos el pago.',
            ],
            'General' => [
                '¿En qué zonas funciona?' => 'Malacatos, que es la zona principal, y sus parroquias vecinas: ' . implode(', ', array_slice(config('marketplace.zones'), 1)) . '.',
                '¿Qué hago si tengo un problema?' => 'Escríbenos por WhatsApp o desde la página de contacto con los detalles y lo revisamos.',
            ],
        ];
    @endphp
    <x-ui.page title="Preguntas" accent="frecuentes" eyebrow="Ayuda" lede="Si no encuentras tu respuesta, escríbenos por WhatsApp y te ayudamos." />

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="grid gap-12 lg:grid-cols-[220px_1fr]">
            @foreach ($faqs as $group => $items)
                <h2 class="lp-eyebrow uppercase tracking-[.14em] text-grafito-400 lg:pt-6">{{ $group }}</h2>
                <div class="-mt-8 lg:mt-0">
                    @foreach ($items as $question => $answer)
                        <details class="group border-t border-humo-300 last:border-b">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6 text-[18px] font-medium [&::-webkit-details-marker]:hidden">
                                {{ $question }}
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-humo-300 transition group-open:rotate-45"><x-site.icon name="plus" class="h-4 w-4" /></span>
                            </summary>
                            <p class="max-w-2xl pb-6 text-[16px] leading-relaxed text-grafito-600">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="mt-16 flex flex-wrap gap-3">
            <a href="{{ route('home.contact') }}" class="ui-btn ui-btn-primary">Escribirnos</a>
            <a href="{{ route('search') }}" class="ui-btn ui-btn-secondary">Buscar servicios</a>
        </div>
    </section>
</div>
