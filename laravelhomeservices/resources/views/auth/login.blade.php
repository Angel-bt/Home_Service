<x-base-layout>
    <x-ui.auth-card title="Hola de" accent="nuevo" lede="Ingresa con el correo y la contraseña de tu cuenta.">
        <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ visible: false, sending: false }" @submit="sending = true">
            @csrf
            <x-ui.field label="Correo electrónico" name="email">
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="ui-input @error('email') ui-input-invalid @enderror">
            </x-ui.field>
            <x-ui.field label="Contraseña" name="password">
                <div class="relative">
                    <input id="password" :type="visible ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="ui-input pr-12 @error('password') ui-input-invalid @enderror">
                    <button type="button" @click="visible = !visible" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-grafito-400 hover:text-grafito" :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"><x-site.icon name="eye" class="h-5 w-5" /></button>
                </div>
            </x-ui.field>
            <div class="flex flex-wrap items-center justify-between gap-3 text-[14px]">
                <label class="flex cursor-pointer items-center gap-2 text-grafito-600"><input type="checkbox" name="remember" class="ui-check"> Recordarme</label>
                <a href="{{ route('password.request') }}" class="ui-link">¿Olvidaste tu contraseña?</a>
            </div>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full" :disabled="sending">
                <span x-show="!sending">Ingresar</span>
                <span x-show="sending" x-cloak>Entrando…</span>
            </button>
        </form>
        <x-slot:footer>
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="ui-link">Créala gratis</a>
        </x-slot:footer>
    </x-ui.auth-card>
</x-base-layout>
