<div>
    {{-- Revisar con un abogado. Si cambia algo de fondo, sube config('marketplace.terms_version'): todas las cuentas vuelven a aceptar. --}}
    <x-ui.page title="Términos" accent="de uso" eyebrow="Legal" lede="Versión vigente desde el {{ \Illuminate\Support\Carbon::parse(config('marketplace.terms_version'))->format('d/m/Y') }}" />
    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="ui-prose">
            <h2>1. Qué es Malakplace</h2>
            <p>Malakplace es una plataforma que solo actúa como intermediario: pone en contacto a personas que buscan un servicio para su hogar con trabajadores independientes que lo ofrecen.</p>
            <p>Malakplace no presta los servicios y no es parte del acuerdo entre cliente y proveedor. Los proveedores no son empleados, socios ni representantes de Malakplace, y usar la plataforma no crea ninguna relación laboral con ella.</p>

            <h2>2. Lo que Malakplace no hace ni responde</h2>
            <p>El cliente y el proveedor deciden entre ellos si trabajan juntos, y qué precio, plazo y forma de pago acuerdan. Malakplace no supervisa los trabajos, no recibe ese dinero y no garantiza la calidad, la seguridad, el resultado ni el cumplimiento de ningún servicio.</p>
            <p>Por eso, en todo lo que la ley permita, Malakplace no responde por lo que ocurra en el trato o en el trabajo entre cliente y proveedor, como:</p>
            <ul>
                <li>robos, pérdidas, daños a personas o cosas;</li>
                <li>trabajos mal hechos, incompletos o que no se hicieron;</li>
                <li>que alguien no llegue, llegue tarde o cancele;</li>
                <li>desacuerdos por precios o pagos;</li>
                <li>la veracidad de lo que cada persona publica o dice.</li>
            </ul>
            <p>Un reclamo por el trabajo se hace directamente a la otra persona o ante las autoridades competentes. Si nos avisas, podemos revisar y suspender la cuenta, y colaboraremos con las autoridades cuando lo pidan. Nada de esto quita los derechos que la ley te reconoce y que no se pueden renunciar.</p>

            <h2>3. Cuentas</h2>
            <p>Debes dar datos reales, ser mayor de edad y mantener tu contraseña en reserva. Eres responsable de lo que se haga con tu cuenta.</p>

            <h2>4. Proveedores</h2>
            <p>Eres un trabajador independiente. Respondes por tu trabajo, por la veracidad de tus servicios, precios y zonas, por contar con los permisos necesarios y por emitir los comprobantes que correspondan según la normativa del SRI.</p>
            <p>Al crear tu cuenta aceptas que se publiquen en tu perfil tu nombre, foto, oficio, zonas, WhatsApp, presentación, años de experiencia y reseñas, y también los sellos que obtengas con su fecha, para que los clientes sepan con quién tratan. Sin este permiso no se puede tener una cuenta de proveedor. Puedes retirarlo eliminando tu cuenta.</p>

            <h2 id="sellos">5. Verificación y sellos</h2>
            <p>Entregar documentos es voluntario. Puedes publicar y trabajar sin sellos, y no tener un sello no significa nada malo sobre una persona.</p>
            <p>Un sello significa solo que la administración revisó un documento entregado por el proveedor, en la fecha que aparece:</p>
            <ul>
                <li><strong>Identidad verificada:</strong> revisamos su cédula.</li>
                <li><strong>Sin antecedentes penales:</strong> revisamos el certificado oficial del Ministerio del Interior que nos entregó, y no registraba antecedentes en esa fecha. Este sello vence y debe renovarse.</li>
                <li><strong>Experiencia revisada:</strong> revisamos su hoja de vida o certificados.</li>
            </ul>
            <p>Los sellos no son una garantía sobre la conducta de una persona ni sobre su trabajo. Malakplace no es una autoridad: no puede asegurar que un documento sea auténtico ni que la situación de alguien no haya cambiado después. Usa tu propio criterio al contratar. Entregar un documento falso causa el cierre de la cuenta.</p>

            <h2>6. Precios, pagos y plan Pro</h2>
            <p>Los precios publicados son referenciales. El precio final y la forma de pago se acuerdan directamente entre cliente y proveedor. Usar Malakplace es gratis para los clientes. Los proveedores pueden contratar el plan Pro, cuyo precio y beneficios se informan antes de pagarlo.</p>

            <h2>7. Reseñas</h2>
            <p>Las reseñas deben ser honestas y referirse a trabajos realizados. Podemos retirar contenido ofensivo, falso o que incluya datos personales de terceros.</p>

            <h2>8. Suspensión de cuentas</h2>
            <p>Podemos suspender o cerrar cuentas con información falsa, que hagan mal uso de la plataforma o que reciban quejas graves. Puedes eliminar tu cuenta cuando quieras desde «Cuenta y contraseña».</p>

            <h2>9. Cambios a estos términos</h2>
            <p>Si los cambiamos en algo importante, te pediremos aceptarlos otra vez la próxima vez que entres. Estos términos se rigen por las leyes de Ecuador.</p>

            <h2>10. Contacto</h2>
            <p>Para cualquier consulta escríbenos a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>.</p>
        </div>
    </section>
</div>
