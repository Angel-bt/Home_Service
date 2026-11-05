<div>
    {{-- Revisar con un abogado frente a la Ley Orgánica de Protección de Datos Personales. --}}
    <x-ui.page title="Política de" accent="privacidad" eyebrow="Legal" lede="Versión vigente desde el {{ \Illuminate\Support\Carbon::parse(config('marketplace.terms_version'))->format('d/m/Y') }}" />
    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="ui-prose">
            <h2>Quién trata tus datos</h2>
            <p>Malakplace, una plataforma de Proyetech. Puedes escribirnos a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>.</p>

            <h2>Datos que recopilamos</h2>
            <p>Nombre, correo, teléfono y, en las solicitudes de servicio, la zona, dirección o referencia y el mensaje que escribas. De los proveedores, además, su WhatsApp, foto, presentación, años de experiencia y zonas de trabajo. También guardamos qué versión de los términos aceptaste y cuándo.</p>

            <h2>Para qué los usamos</h2>
            <ul>
                <li>Para que el proveedor que elegiste pueda contactarte y atender tu solicitud.</li>
                <li>Para mostrar los perfiles y servicios de los proveedores.</li>
                <li>Para enviarte correos sobre tu cuenta y tus solicitudes.</li>
                <li>Para entender cómo se usa el sitio y mejorarlo.</li>
            </ul>

            <h2>Documentos de verificación de proveedores</h2>
            <p>Si un proveedor decide verificarse, puede entregarnos su cédula, su hoja de vida y su certificado de antecedentes penales. Este certificado es un dato sensible: solo lo tratamos con su autorización expresa y con el único fin de dar el sello correspondiente.</p>
            <p>Los documentos se guardan en un almacenamiento privado, solo los ve la administración y <strong>se borran apenas se revisan</strong>. Conservamos solo el resultado, la fecha y quién revisó. Nunca se publican: en el perfil solo aparece el sello y su fecha. El proveedor puede retirar su permiso en cualquier momento desde «Verificarme»: borramos el documento y el sello deja de aparecer.</p>

            <h2>Registro de actividad</h2>
            <p>Guardamos qué se hace en el sitio: lo que se busca, qué servicios se visitan, los toques en el botón de WhatsApp, las solicitudes, los registros y los ingresos. Si tienes una cuenta, queda asociado a ella. No guardamos tu dirección IP para esto. Lo usamos para saber qué oficios faltan y para mostrar a cada proveedor cuántas visitas tuvo. Los datos se borran después de {{ config('marketplace.activity_retention_days') }} días, y al eliminar tu cuenta dejan de estar asociados a ti.</p>

            <h2>Con quién los compartimos</h2>
            <p>Tus datos de una solicitud solo los ve el proveedor de ese servicio y la administración. Los datos del perfil de un proveedor son públicos. No vendemos tus datos.</p>
            <p>Usamos proveedores de tecnología para funcionar: alojamiento (Vercel), base de datos y archivos (Supabase) y correo (Google). Sus servidores pueden estar fuera de Ecuador, por ejemplo en Estados Unidos.</p>

            <h2>Tus derechos</h2>
            <p>Puedes pedir acceso, corrección o eliminación de tus datos, oponerte a su uso o retirar tu consentimiento escribiendo a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>, sin costo y sin dar explicaciones. También puedes eliminar tu cuenta desde «Cuenta y contraseña».</p>
        </div>
    </section>
</div>
