<x-base-layout>
    <x-ui.auth-card title="¿Olvidaste tu" accent="contraseña?" lede="No pasa nada. Escribe tu correo y te enviaremos un enlace para crear una nueva.">
        @if (config('mail.default') === 'log')
            @php $help = \App\Support\WhatsApp::link(config('marketplace.contact_phone'), 'Hola, olvidé mi contraseña de Malakplace.'); @endphp
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-[15px] text-amber-950" role="status">
                <p>Por ahora recuperamos las contraseñas por WhatsApp. Escríbenos y te ayudamos enseguida.</p>
                @if ($help)<a href="{{ $help }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm mt-3"><x-site.icon name="whatsapp" class="h-4 w-4" /> Escribir por WhatsApp</a>@endif
            </div>
        @endif
        @if (session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-[15px] text-emerald-900" role="status">
                <x-site.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" />
                <div>
                    <p>{{ session('status') }}</p>
                    <p class="mt-1 text-[14px] text-emerald-900/80">Revisa también la carpeta de correo no deseado.</p>
                </div>
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5 {{ config('mail.default') === 'log' ? 'hidden' : '' }}">
            @csrf
            <x-ui.field label="Tu correo" name="email">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nombre@correo.com" class="ui-input @error('email') ui-input-invalid @enderror">
            </x-ui.field>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full">Enviarme el enlace</button>
        </form>
        <x-slot:footer>
            <a href="{{ route('login') }}" class="ui-link">Volver a ingresar</a>
        </x-slot:footer>
    </x-ui.auth-card>
</x-base-layout>
