{{-- Modal de acceso (login + registro) con Alpine. Se abre con: $dispatch('open-auth', { mode: 'login'|'register', as: 'CST'|'SVP' }). --}}
@php
    $hasErrors = $errors->any();
    $startMode = old('registeras') || old('name') ? 'register' : 'login';
    $field = 'w-full rounded-xl border border-humo-300 bg-white px-4 py-3 text-[15px] text-grafito placeholder-grafito-400 transition focus:border-noche-700 focus:outline-none focus:ring-4 focus:ring-noche-700/10';
    $label = 'mb-1.5 block text-[13px] font-medium text-grafito-600';
@endphp
<div x-data="{
        open: {{ $hasErrors ? 'true' : 'false' }},
        mode: '{{ $startMode }}',
        as: '{{ old('registeras', 'CST') }}',
        show(detail) { this.mode = detail?.mode || 'login'; if (detail?.as) this.as = detail.as; this.open = true; },
     }"
     x-init="window.lpAuth = true"
     @open-auth.window="show($event.detail)"
     @keydown.escape.window="open = false"
     x-effect="document.documentElement.style.overflow = open ? 'hidden' : ''">
    <div x-cloak x-show="open" x-transition.opacity.duration.300ms class="fixed inset-0 z-[75] bg-noche-950/70 backdrop-blur-sm" @click="open = false"></div>
    <div x-cloak x-show="open" x-trap.noscroll="open"
         x-transition:enter="transition duration-500 ease-[cubic-bezier(.2,.8,.2,1)]" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-x-0 bottom-0 z-[76] max-h-[92vh] overflow-y-auto rounded-t-[28px] bg-humo-50 shadow-2xl sm:inset-auto sm:left-1/2 sm:top-1/2 sm:w-[440px] sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-[28px]"
         role="dialog" aria-modal="true" aria-labelledby="lp-auth-title">
        <div class="flex items-start justify-between px-7 pt-7">
            <div>
                <p class="lp-eyebrow uppercase text-grafito-400">Malakplace</p>
                <h2 id="lp-auth-title" class="lp-title mt-2 text-[28px] text-grafito">
                    <span x-show="mode === 'login'">Hola de <span class="lp-accent">nuevo</span></span>
                    <span x-show="mode === 'register'" x-cloak>Crea tu <span class="lp-accent">cuenta</span></span>
                </h2>
            </div>
            <button type="button" @click="open = false" class="grid h-9 w-9 place-items-center rounded-full border border-humo-300 text-grafito-500 transition hover:border-grafito hover:text-grafito" aria-label="Cerrar">
                <x-site.icon name="close" class="h-4 w-4" />
            </button>
        </div>

        <div class="relative mx-7 mt-6 grid grid-cols-2 rounded-full bg-humo-200 p-1 text-[13px] font-medium">
            <span class="absolute inset-y-1 left-1 w-[calc(50%-4px)] rounded-full bg-white shadow-sm transition-transform duration-300" :class="mode === 'register' && 'translate-x-full'" aria-hidden="true"></span>
            <button type="button" @click="mode = 'login'" class="relative rounded-full py-2 transition" :class="mode === 'login' ? 'text-grafito' : 'text-grafito-500'">Iniciar sesión</button>
            <button type="button" @click="mode = 'register'" class="relative rounded-full py-2 transition" :class="mode === 'register' ? 'text-grafito' : 'text-grafito-500'">Crear cuenta</button>
        </div>

        <div class="px-7 pb-8 pt-6">
            @if ($hasErrors)
                <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $errors->first() }}</div>
            @endif

            <form x-show="mode === 'login'" method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="lp-login-email" class="{{ $label }}">Correo electrónico</label>
                    <input id="lp-login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $field }}">
                </div>
                <div>
                    <label for="lp-login-password" class="{{ $label }}">Contraseña</label>
                    <input id="lp-login-password" type="password" name="password" required autocomplete="current-password" class="{{ $field }}">
                </div>
                <div class="flex items-center justify-between text-[13px]">
                    <label class="flex items-center gap-2 text-grafito-500"><input type="checkbox" name="remember" class="rounded border-humo-300 text-noche-700 focus:ring-noche-700"> Recordarme</label>
                    <a href="{{ route('password.request') }}" class="font-medium text-grafito underline-offset-4 hover:underline">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="w-full rounded-full bg-noche-900 py-3.5 text-[13px] font-medium uppercase tracking-[.08em] text-white transition hover:bg-noche-700">Entrar</button>
            </form>

            <form x-cloak x-show="mode === 'register'" method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Tipo de cuenta">
                    @foreach ([['CST', 'Busco servicios', 'Cuenta de cliente'], ['SVP', 'Ofrezco servicios', 'Cuenta de proveedor']] as [$value, $title, $hint])
                        <label class="cursor-pointer rounded-2xl border p-3.5 text-sm transition" :class="as === '{{ $value }}' ? 'border-noche-900 bg-white shadow-sm' : 'border-humo-300'">
                            <input type="radio" name="registeras" value="{{ $value }}" x-model="as" class="sr-only">
                            <span class="block font-medium text-grafito">{{ $title }}</span>
                            <span class="text-xs text-grafito-500">{{ $hint }}</span>
                        </label>
                    @endforeach
                </div>
                <div>
                    <label for="lp-reg-name" class="{{ $label }}">Nombre completo</label>
                    <input id="lp-reg-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $field }}">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="lp-reg-email" class="{{ $label }}">Correo</label>
                        <input id="lp-reg-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="lp-reg-phone" class="{{ $label }}">Celular</label>
                        <input id="lp-reg-phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="0991234567" class="{{ $field }}">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="lp-reg-password" class="{{ $label }}">Contraseña</label>
                        <input id="lp-reg-password" type="password" name="password" required autocomplete="new-password" class="{{ $field }}">
                    </div>
                    <div>
                        <label for="lp-reg-password2" class="{{ $label }}">Repítela</label>
                        <input id="lp-reg-password2" type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $field }}">
                    </div>
                </div>
                <button type="submit" class="w-full rounded-full bg-noche-900 py-3.5 text-[13px] font-medium uppercase tracking-[.08em] text-white transition hover:bg-noche-700">Crear cuenta gratis</button>
                <p class="text-center text-xs text-grafito-400">Al registrarte aceptas los <a href="{{ route('home.terms') }}" class="underline">términos</a> y la <a href="{{ route('home.privacy') }}" class="underline">privacidad</a>.</p>
            </form>
        </div>
    </div>
</div>
