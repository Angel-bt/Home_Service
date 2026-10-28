<div>
    @include('livewire.partials.page-header', ['title' => 'Política de privacidad'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    <div class="row">
                        <div class="col-md-10 col-md-offset-1" style="font-size: 16px; line-height: 1.7;">
                            {{-- Borrador: revisar frente a la Ley Orgánica de Protección de Datos Personales antes de producción. --}}
                            <p><em>Última actualización: {{ now()->format('d/m/Y') }}</em></p>
                            <h3>Datos que recopilamos</h3>
                            <p>Nombre, correo, teléfono y, en las solicitudes de servicio, la zona, dirección o referencia y el mensaje que escribas. De los proveedores, además, su WhatsApp, foto, presentación y zonas de trabajo.</p>
                            <h3>Para qué los usamos</h3>
                            <ul class="list-styles">
                                <li><i class="fa fa-check"></i> Para que el proveedor que elegiste pueda contactarte y atender tu solicitud.</li>
                                <li><i class="fa fa-check"></i> Para mostrar los perfiles y servicios de los proveedores.</li>
                                <li><i class="fa fa-check"></i> Para enviarte correos relacionados con tu cuenta.</li>
                            </ul>
                            <h3>Con quién los compartimos</h3>
                            <p>Tus datos de una solicitud solo los ve el proveedor de ese servicio y la administración. No vendemos tus datos.</p>
                            <h3>Tus derechos</h3>
                            <p>Puedes pedir acceso, corrección o eliminación de tus datos escribiendo a <a href="mailto:{{ config('marketplace.contact_email') }}">{{ config('marketplace.contact_email') }}</a>, o eliminar tu cuenta desde tu perfil.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
