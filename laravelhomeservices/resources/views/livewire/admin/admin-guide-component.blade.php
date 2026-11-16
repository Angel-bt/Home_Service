<div>
    @php
        $sections = [
            'El día a día' => [
                ['Revisar lo pendiente', 'Entra a «Inicio». Arriba aparece todo lo que espera por ti: pagos, documentos, mensajes, solicitudes atrasadas y reseñas malas. Si dice «Todo al día», no hay nada que hacer.', 'admin.dashboard'],
                ['Aprobar un pago del plan Pro', 'En «Pagos Pro», abre el comprobante y confirma en tu banco que el dinero llegó. Toca «Aprobar y activar Pro». Si no llegó, «Rechazar» y escribe el motivo: el proveedor lo verá. Si te pagaron en efectivo en persona, usa «Registrar pago en efectivo».', 'admin.payments'],
                ['Dar un sello de verificación', 'En «Verificaciones», abre el documento y compáralo con lo que dice «Qué revisar». Toca «Dar el sello» o «No dar el sello» (con un motivo amable, sin detalles del certificado). El archivo se borra apenas decides.', 'admin.verifications'],
                ['Responder un mensaje', 'En «Mensajes», responde por WhatsApp o correo y toca «Ya respondí» para que no quede pendiente.', 'admin.contacts'],
                ['Ayudar con una solicitud atrasada', 'En «Solicitudes» verás las que llevan más de un día sin respuesta. Escríbele al proveedor para recordarle, o al cliente para ofrecerle otra opción.', 'admin.service_requests'],
            ],
            'Personas' => [
                ['Buscar a alguien', 'En «Usuarios» escribe parte del nombre, del correo o del celular.', 'admin.users'],
                ['Suspender una cuenta', 'En «Usuarios», toca «Suspender» y escribe el motivo. La persona no podrá entrar (verá el motivo) y sus servicios dejan de verse. «Reactivar» lo deshace.', 'admin.users'],
                ['Dar acceso de administración a otra persona', 'Pídele que cree su cuenta en el sitio. Luego, en «Usuarios», «Cambiar tipo» → «Hacerlo administración». No puedes quitarte el acceso a ti mismo ni dejar el sitio sin administración.', 'admin.users'],
                ['Eliminar una cuenta', 'En «Usuarios», «Eliminar cuenta». Se borran sus servicios, solicitudes y documentos y no se puede deshacer. Si es por una falta, mejor suspende.', 'admin.users'],
            ],
            'Catálogo' => [
                ['Agregar o cambiar un oficio', 'En «Categorías», «Agregar categoría» con nombre y una foto. Borrar un oficio borra también sus servicios.', 'admin.service_categories'],
                ['Ocultar o destacar un servicio', 'En «Servicios», «Ocultar» lo quita del sitio sin borrarlo; «Destacar» lo pone primero en las listas.', 'admin.all_services'],
                ['Quitar una reseña ofensiva', 'En «Reseñas», «Ocultar» con un motivo. Una mala calificación honesta debe quedarse.', 'admin.reviews'],
            ],
            'Lo que dice el sitio' => [
                ['Cambiar los textos de la portada', 'En «Textos y aviso». Lo que escribas entre *asteriscos* sale destacado. Toca «Guardar» y mira la portada.', 'admin.content'],
                ['Poner un aviso (feriado, vacaciones…)', 'En «Textos y aviso» → «Aviso destacado»: marca «Mostrar el aviso», escribe el texto y guarda. Para quitarlo, desmárcalo.', 'admin.content'],
                ['Corregir los términos o la privacidad', 'En «Textos legales». Para una errata o un dato: «Guardar corrección». Para un cambio de fondo (algo nuevo que la gente acepta): «Publicar nueva versión», y todos aceptarán de nuevo al entrar. Que lo revise un abogado antes.', 'admin.legal'],
                ['Editar las preguntas frecuentes', 'En «Preguntas frecuentes»: agregar, editar, ordenar con las flechas y quitar.', 'admin.faq'],
                ['Cambiar teléfono, correo, precios, banco o zonas', 'En «Ajustes». El precio Pro, el límite gratis, las formas de pago y los datos bancarios se aplican al instante.', 'admin.settings'],
            ],
            'Si algo falla' => [
                ['Revisar que todo funcione', 'En «Estado del sistema», toca «Revisar ahora». Todo en verde: no hay problema. Si algo sale en rojo, el texto dice qué revisar.', 'admin.system'],
                ['Ver los errores', 'En «Errores» quedan agrupados los fallos del sitio. Si uno se repite, toca «Copiar el detalle» y envíaselo a quien mantiene el sitio.', 'admin.errors'],
                ['Saber quién cambió algo', 'En «Historial» queda cada acción del panel con la persona, la hora y desde dónde. Si ves algo que no hiciste, cambia tu contraseña y cierra las otras sesiones en «Cuenta y contraseña».', 'admin.history'],
                ['Guardar una copia de los datos', 'En «Estado del sistema» → «Descargar datos» (para Excel). Tiene datos personales: guárdala en un lugar seguro.', 'admin.system'],
                ['Proteger tu cuenta', 'Activa la verificación en dos pasos en «Cuenta y contraseña». Cada vez que alguien entra como administración te llega un correo.', 'profile.show'],
            ],
        ];
    @endphp

    <x-ui.panel title="Guía" accent="del administrador" help="Cómo hacer cada cosa desde este panel, sin tocar el código. Toca el título de una tarea para ir a esa pantalla.">
        <div class="space-y-8">
            @foreach ($sections as $section => $tasks)
                <section>
                    <h2 class="lp-title mb-4 text-2xl">{{ $section }}</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach ($tasks as [$title, $how, $route])
                            <article class="ui-card ui-card-pad">
                                <h3 class="text-[17px] font-medium"><a href="{{ route($route) }}" class="hover:underline">{{ $title }}</a></h3>
                                <p class="mt-2 text-[15px] leading-relaxed text-grafito-600">{{ $how }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section class="rounded-3xl bg-noche-900 p-6 text-white sm:p-8">
                <h2 class="lp-title text-2xl">Lo que sí necesita a alguien técnico</h2>
                <p class="mt-2 max-w-3xl text-[15px] leading-relaxed text-white/80">Cambiar el diseño o agregar funciones nuevas, las claves secretas (correo, almacenamiento, base de datos) que están en Vercel, y un dominio propio. Para todo lo demás, este panel alcanza.</p>
            </section>
        </div>
    </x-ui.panel>
</div>
