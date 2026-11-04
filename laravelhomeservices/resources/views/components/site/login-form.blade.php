{{-- Formulario de ingreso (página /login y modal). «volver» trae al cliente de regreso a la página donde estaba. --}}
<form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ visible: false, sending: false }" @submit="sending = true" {{ $attributes }}>
    @csrf
    <input type="hidden" name="volver" value="{{ old('volver', request()->routeIs('login', 'register') ? '' : request()->getRequestUri()) }}">
    <x-ui.field label="Tu correo" name="email" for="login-email">
        <input id="login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autocapitalize="none" inputmode="email" class="ui-input @error('email') ui-input-invalid @enderror">
    </x-ui.field>
    <x-ui.field label="Tu contraseña" name="password" for="login-password">
        <div class="relative">
            <input id="login-password" :type="visible ? 'text' : 'password'" type="password" name="password" required autocomplete="current-password" class="ui-input pr-12 @error('password') ui-input-invalid @enderror">
            <button type="button" @click="visible = !visible" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-grafito-400 hover:text-grafito" :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"><x-site.icon name="eye" class="h-5 w-5" /></button>
        </div>
    </x-ui.field>
    <div class="flex flex-wrap items-center justify-between gap-3 text-[14px]">
        <label class="flex cursor-pointer items-center gap-2 text-grafito-600"><input type="checkbox" name="remember" class="ui-check" checked> Recordarme</label>
        <a href="{{ route('password.request') }}" class="ui-link">¿Olvidaste tu contraseña?</a>
    </div>
    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full" :disabled="sending">
        <span x-show="!sending">Ingresar</span>
        <span x-show="sending" x-cloak>Entrando…</span>
    </button>
</form>
