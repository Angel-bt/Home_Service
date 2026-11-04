<div>
    {{-- Borrador: revisar con un profesional legal antes de publicar en producción. --}}
    <x-ui.page title="Términos" accent="de uso" eyebrow="Legal" lede="Última actualización: {{ now()->format('d/m/Y') }}" />
    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="ui-prose">
            <h2>1. Qué es Malakplace</h2>
            <p>Malakplace es una plataforma que pone en contacto a clientes con proveedores independientes de servicios para el hogar. Malakplace no presta los servicios ni es parte del acuerdo entre cliente y proveedor.</p>
            <h2>2. Cuentas</h2>
            <p>Debes dar datos reales y mantener tu contraseña en reserva. Podemos suspender cuentas con información falsa o que hagan mal uso de la plataforma.</p>
            <h2>3. Proveedores</h2>
            <p>Los proveedores son responsables de la veracidad de sus servicios, precios y zonas, de contar con los permisos necesarios y de emitir los comprobantes que correspondan según la normativa del SRI.</p>
            <h2>4. Precios y pagos</h2>
            <p>Los precios publicados son referenciales. El precio final y la forma de pago se acuerdan directamente entre cliente y proveedor. Los planes de pago para proveedores, cuando existan, se informarán antes de contratarlos.</p>
            <h2>5. Reseñas</h2>
            <p>Las reseñas deben ser honestas y referirse a trabajos realizados. Podemos retirar contenido ofensivo, falso o que incluya datos personales de terceros.</p>
            <h2>6. Contacto</h2>
            <p>Para cualquier consulta escríbenos a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>.</p>
        </div>
    </section>
</div>
