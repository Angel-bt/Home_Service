<div>
    @include('livewire.partials.page-header', ['title' => 'Términos de uso'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1" style="font-size: 16px; line-height: 1.7;">
                            {{-- Borrador: revisar con un profesional legal antes de publicar en producción. --}}
                            <p><em>Última actualización: {{ now()->format('d/m/Y') }}</em></p>
                            <h3>1. Qué es Proyetech</h3>
                            <p>Proyetech es una plataforma que pone en contacto a clientes con proveedores independientes de servicios para el hogar. Proyetech no presta los servicios ni es parte del acuerdo entre cliente y proveedor.</p>
                            <h3>2. Cuentas</h3>
                            <p>Debes dar datos reales y mantener tu contraseña en reserva. Podemos suspender cuentas con información falsa o que hagan mal uso de la plataforma.</p>
                            <h3>3. Proveedores</h3>
                            <p>Los proveedores son responsables de la veracidad de sus servicios, precios y zonas, de contar con los permisos necesarios y de emitir los comprobantes que correspondan según la normativa del SRI.</p>
                            <h3>4. Precios y pagos</h3>
                            <p>Los precios publicados son referenciales. El precio final y la forma de pago se acuerdan directamente entre cliente y proveedor. Los planes de pago para proveedores, cuando existan, se informarán antes de contratarlos.</p>
                            <h3>5. Reseñas</h3>
                            <p>Las reseñas deben ser honestas y referirse a trabajos realizados. Podemos retirar contenido ofensivo, falso o que incluya datos personales de terceros.</p>
                            <h3>6. Contacto</h3>
                            <p>Para cualquier consulta escríbenos a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
