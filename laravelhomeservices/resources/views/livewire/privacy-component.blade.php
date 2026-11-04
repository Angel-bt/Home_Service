<div>
    {{-- Borrador: revisar frente a la Ley Orgánica de Protección de Datos Personales antes de producción. --}}
    <x-ui.page title="Política de" accent="privacidad" eyebrow="Legal" lede="Última actualización: {{ now()->format('d/m/Y') }}" />
    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="ui-prose">
            <h2>Datos que recopilamos</h2>
            <p>Nombre, correo, teléfono y, en las solicitudes de servicio, la zona, dirección o referencia y el mensaje que escribas. De los proveedores, además, su WhatsApp, foto, presentación y zonas de trabajo.</p>
            <h2>Para qué los usamos</h2>
            <ul>
                <li>Para que el proveedor que elegiste pueda contactarte y atender tu solicitud.</li>
                <li>Para mostrar los perfiles y servicios de los proveedores.</li>
                <li>Para enviarte correos relacionados con tu cuenta.</li>
            </ul>
            <h2>Con quién los compartimos</h2>
            <p>Tus datos de una solicitud solo los ve el proveedor de ese servicio y la administración. No vendemos tus datos.</p>
            <h2>Tus derechos</h2>
            <p>Puedes pedir acceso, corrección o eliminación de tus datos escribiendo a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>, o eliminar tu cuenta desde «Cuenta y contraseña».</p>
        </div>
    </section>
</div>
