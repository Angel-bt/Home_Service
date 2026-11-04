<div>
    <x-ui.page title="Gente de oficio del valle," accent="a un mensaje." eyebrow="Nosotros"
               lede="Malakplace conecta a familias, dueños de quintas y negocios de Malacatos y sus parroquias vecinas (Vilcabamba, Yangana y Quinara) con proveedores locales: gasfitería, electricidad, jardinería, piscinas, albañilería, limpieza y mucho más." />

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="grid gap-10 md:grid-cols-2">
            <div class="ui-card ui-card-pad">
                <p class="lp-eyebrow uppercase tracking-[.14em] text-grafito-400">Si buscas un servicio</p>
                <ul class="mt-6 space-y-4 text-[16px]">
                    @foreach (['Encuentras proveedores que trabajan en tu zona.', 'Les escribes directo por WhatsApp o les envías una solicitud.', 'Lees las reseñas de quienes ya los contrataron.', 'Acuerdas el precio y le pagas directo al proveedor.'] as $line)
                        <li class="flex gap-3"><x-site.icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" /> {{ $line }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('search') }}" class="ui-btn ui-btn-primary mt-8">Buscar servicios</a>
            </div>
            <div class="ui-card ui-card-pad">
                <p class="lp-eyebrow uppercase tracking-[.14em] text-grafito-400">Si ofreces un servicio</p>
                <ul class="mt-6 space-y-4 text-[16px]">
                    @foreach (['Creas tu cuenta como proveedor.', 'Completas tu perfil con tu WhatsApp y las zonas donde trabajas.', 'Publicas hasta ' . config('marketplace.free_service_limit') . ' servicios gratis.', 'Los clientes te escriben a ti; tú pones el precio y cobras directo.'] as $line)
                        <li class="flex gap-3"><x-site.icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" /> {{ $line }}</li>
                    @endforeach
                </ul>
                <div class="mt-8 flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('register') }}" @click="if (window.lpAuth) { $event.preventDefault(); $dispatch('open-auth', { mode: 'register', as: 'SVP' }) }" class="ui-btn ui-btn-gold">Quiero ofrecer servicios</a>
                    @endguest
                    <a href="{{ route('home.contact') }}" class="ui-btn ui-btn-secondary">Contáctanos</a>
                </div>
            </div>
        </div>
    </section>
</div>
