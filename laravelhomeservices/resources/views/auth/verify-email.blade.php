<x-base-layout>
    <x-ui.auth-card title="Revisa tu" accent="correo" lede="Te enviamos un enlace para confirmar tu cuenta. Ábrelo y listo. Si no lo ves, mira en el correo no deseado.">
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-[15px] text-emerald-900" role="status">
                <x-site.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" />
                <p>Te enviamos un enlace nuevo al correo que registraste.</p>
            </div>
        @endif
        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-primary">Enviarme otro enlace</button>
            </form>
            <a href="{{ route('profile.show') }}" class="ui-btn ui-btn-ghost">Corregir mi correo</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-ghost">Cerrar sesión</button>
            </form>
        </div>
    </x-ui.auth-card>
</x-base-layout>
