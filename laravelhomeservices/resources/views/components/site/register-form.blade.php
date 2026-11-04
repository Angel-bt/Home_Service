{{-- Formulario de registro (página /register y modal). Solo lo necesario: una contraseña que se puede ver, sin repetirla.
     «volver» trae al cliente de regreso a la página donde estaba. --}}
@props(['as' => 'CST'])
<form method="POST" action="{{ route('register') }}" class="space-y-5"
      x-data="{ as: @js(old('registeras', $as)), visible: false, sending: false }"
      @open-auth.window="if ($event.detail?.as) as = $event.detail.as"
      @submit="sending = true" {{ $attributes }}>
    @csrf
    <input type="hidden" name="volver" value="{{ old('volver', request()->routeIs('login', 'register') ? '' : request()->getRequestUri()) }}">
    <fieldset>
        <legend class="ui-label">¿Para qué quieres tu cuenta?</legend>
        <div class="grid grid-cols-2 gap-2">
            @foreach ([['CST', 'Busco servicios', 'Quiero contratar'], ['SVP', 'Ofrezco servicios', 'Busco clientes']] as [$value, $optionTitle, $hint])
                <label class="cursor-pointer rounded-2xl border p-4 transition" :class="as === '{{ $value }}' ? 'border-noche-900 bg-humo-50 ring-1 ring-noche-900' : 'border-humo-300 hover:border-grafito-400'">
                    <input type="radio" name="registeras" value="{{ $value }}" x-model="as" class="sr-only">
                    <span class="block text-[15px] font-medium leading-tight">{{ $optionTitle }}</span>
                    <span class="mt-1 block text-[13px] text-grafito-500">{{ $hint }}</span>
                </label>
            @endforeach
        </div>
        @error('registeras')
            <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
        @enderror
    </fieldset>
    <x-ui.field label="Tu nombre y apellido" name="name" for="register-name">
        <input id="register-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autocapitalize="words" class="ui-input @error('name') ui-input-invalid @enderror">
    </x-ui.field>
    <x-ui.field label="Tu celular" name="phone" for="register-phone" hint="Con este número te contactan por WhatsApp.">
        <input id="register-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="0991234567" class="ui-input @error('phone') ui-input-invalid @enderror">
    </x-ui.field>
    <x-ui.field label="Tu correo" name="email" for="register-email">
        <input id="register-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autocapitalize="none" inputmode="email" placeholder="nombre@correo.com" class="ui-input @error('email') ui-input-invalid @enderror">
    </x-ui.field>
    <x-ui.field label="Crea una contraseña" name="password" for="register-password" hint="Mínimo 8 letras o números. Toca el ojo para verla.">
        <div class="relative">
            <input id="register-password" :type="visible ? 'text' : 'password'" type="password" name="password" required minlength="8" autocomplete="new-password" class="ui-input pr-12 @error('password') ui-input-invalid @enderror">
            <button type="button" @click="visible = !visible" class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-grafito-400 hover:text-grafito" :aria-label="visible ? 'Ocultar contraseña' : 'Mostrar contraseña'"><x-site.icon name="eye" class="h-5 w-5" /></button>
        </div>
    </x-ui.field>
    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full" :disabled="sending">
        <span x-show="!sending">Crear mi cuenta gratis</span>
        <span x-show="sending" x-cloak>Creando tu cuenta…</span>
    </button>
    <p class="text-center text-[13px] text-grafito-500">Al crear tu cuenta aceptas los <a href="{{ route('home.terms') }}" class="ui-link">términos</a> y la <a href="{{ route('home.privacy') }}" class="ui-link">privacidad</a>.</p>
</form>
