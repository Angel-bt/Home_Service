<x-base-layout>
    <x-ui.auth-card title="¿Olvidaste tu" accent="contraseña?" lede="No pasa nada. Escribe tu correo y te enviaremos un enlace para crear una nueva.">
        @if (session('status'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-[15px] text-emerald-900" role="status">
                <x-site.icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" />
                <div>
                    <p>{{ session('status') }}</p>
                    <p class="mt-1 text-[14px] text-emerald-900/80">Revisa también la carpeta de correo no deseado.</p>
                </div>
            </div>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
