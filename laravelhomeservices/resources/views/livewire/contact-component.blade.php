<div>
    @php $whatsapp = \App\Support\WhatsApp::link(config('marketplace.contact_phone'), 'Hola, tengo una consulta sobre Malakplace.'); @endphp
    <x-ui.page title="¿Una duda?" accent="Escríbenos." eyebrow="Contacto"
               lede="Atendemos desde Malacatos. Por WhatsApp respondemos el mismo día; por este formulario, en uno o dos días." />

    <section class="mx-auto grid max-w-[84rem] gap-10 px-5 py-12 sm:px-8 lg:grid-cols-[1fr_1.4fr] lg:gap-16 lg:py-16">
        <div class="space-y-8">
            @if ($whatsapp)
                <div class="ui-card ui-card-pad">
                    <p class="text-[15px] font-medium">Lo más rápido</p>
                    <p class="mt-1 text-[15px] text-grafito-500">Escríbenos por WhatsApp y te respondemos hoy.</p>
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp mt-5"><x-site.icon name="whatsapp" class="h-5 w-5" /> {{ config('marketplace.contact_phone') }}</a>
                </div>
            @endif
            <dl class="space-y-5 text-[15px]">
                <div class="flex gap-4"><x-site.icon name="pin" class="h-5 w-5 shrink-0 text-grafito-400" /><div><dt class="text-grafito-500">Dónde estamos</dt><dd class="mt-0.5">Malacatos, Loja · Ecuador</dd></div></div>
                @if (config('marketplace.contact_email'))
                    <div class="flex gap-4"><x-site.icon name="mail" class="h-5 w-5 shrink-0 text-grafito-400" /><div><dt class="text-grafito-500">Correo</dt><dd class="mt-0.5"><a href="mailto:{{ config('marketplace.contact_email') }}" class="ui-link break-all">{{ config('marketplace.contact_email') }}</a></dd></div></div>
                @endif
            </dl>
        </div>

        <div class="ui-card ui-card-pad">
            <h2 class="lp-title text-2xl">Envíanos un mensaje</h2>
            <x-ui.flash class="mt-6" />
            <form wire:submit="sendMessage" class="mt-6 grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Tu nombre" name="name" for="c-name">
                    <input type="text" id="c-name" class="ui-input @error('name') ui-input-invalid @enderror" wire:model="name" autocomplete="name">
                </x-ui.field>
                <x-ui.field label="Tu celular" name="phone" for="c-phone">
                    <input type="tel" id="c-phone" class="ui-input @error('phone') ui-input-invalid @enderror" wire:model="phone" placeholder="0991234567" autocomplete="tel">
                </x-ui.field>
                <x-ui.field label="Tu correo" name="email" for="c-email" class="sm:col-span-2" hint="Te responderemos a este correo.">
                    <input type="email" id="c-email" class="ui-input @error('email') ui-input-invalid @enderror" wire:model="email" autocomplete="email">
                </x-ui.field>
                <x-ui.field label="Tu mensaje" name="message" for="c-message" class="sm:col-span-2">
                    <textarea id="c-message" rows="5" class="ui-input @error('message') ui-input-invalid @enderror" wire:model="message" placeholder="Cuéntanos en qué te ayudamos"></textarea>
                </x-ui.field>
                <div class="sm:col-span-2">
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" wire:loading.attr="disabled" wire:target="sendMessage">
                        <span wire:loading.remove wire:target="sendMessage">Enviar mensaje</span>
                        <span wire:loading wire:target="sendMessage">Enviando…</span>
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>
