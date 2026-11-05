{{-- Lo esencial de los términos, en palabras simples, antes de crear una cuenta. Necesita `as` (CST|SVP) en el x-data del padre. --}}
<div class="max-h-64 space-y-3 overflow-y-auto rounded-2xl border border-humo-300 bg-humo-50 p-4 text-[14px] leading-relaxed text-grafito-600" tabindex="0" aria-label="Resumen de los términos">
    <div>
        <p class="font-medium text-grafito">Malakplace solo conecta a las personas</p>
        <p>Ponemos en contacto a clientes con trabajadores independientes. No son empleados nuestros y no hacemos los trabajos.</p>
    </div>
    <div>
        <p class="font-medium text-grafito">Todo se acuerda entre ustedes</p>
        <p>El precio, el trabajo y el pago se arreglan directamente entre cliente y proveedor. Malakplace no recibe ese dinero.</p>
    </div>
    <div>
        <p class="font-medium text-grafito">No respondemos por lo que pase en el trabajo</p>
        <p>Malakplace no responde por robos, daños, trabajos mal hechos, pagos ni si alguien no llega. Un reclamo se hace a la otra persona o ante las autoridades. Si nos avisas, podemos suspender esa cuenta.</p>
    </div>
    <div>
        <p class="font-medium text-grafito">Los sellos no son una garantía</p>
        <p>Un sello dice que revisamos un documento en esa fecha, nada más. Es voluntario: no tenerlo no significa nada malo.</p>
    </div>
    <div x-show="as === 'SVP'" x-cloak>
        <p class="font-medium text-grafito">Tu perfil será público</p>
        <p>Tu nombre, foto, oficio, zonas, WhatsApp, presentación, años de experiencia y reseñas se publican para que los clientes sepan con quién tratan. Los documentos que entregues para verificarte nunca se publican: solo el sello y su fecha.</p>
    </div>
    <div x-show="as !== 'SVP'">
        <p class="font-medium text-grafito">Cuídate al contratar</p>
        <p>Mira las reseñas y los sellos, acuerden el precio por escrito antes de empezar y no pagues todo por adelantado.</p>
    </div>
</div>
