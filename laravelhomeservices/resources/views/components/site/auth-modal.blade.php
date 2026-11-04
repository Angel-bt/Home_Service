{{-- Modal de acceso (ingreso + registro) con Alpine. Se abre con: $dispatch('open-auth', { mode: 'login'|'register', as: 'CST'|'SVP' }). --}}
@php
    $hasErrors = $errors->any();
    $startMode = old('registeras') || old('name') ? 'register' : 'login';
@endphp
<div x-data="{
        open: {{ $hasErrors ? 'true' : 'false' }},
        mode: '{{ $startMode }}',
        show(detail) { this.mode = detail?.mode || 'login'; this.open = true; },
     }"
     x-init="window.lpAuth = true"
     @open-auth.window="show($event.detail)"
     @keydown.escape.window="open = false"
     x-effect="document.documentElement.style.overflow = open ? 'hidden' : ''">
    <div x-cloak x-show="open" x-transition.opacity.duration.300ms class="fixed inset-0 z-[75] bg-noche-950/70 backdrop-blur-sm" @click="open = false"></div>
    <div x-cloak x-show="open" x-trap.noscroll="open"
         x-transition:enter="transition duration-500 ease-[cubic-bezier(.2,.8,.2,1)]" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-x-0 bottom-0 z-[76] max-h-[92vh] overflow-y-auto rounded-t-[28px] bg-white shadow-2xl sm:inset-auto sm:left-1/2 sm:top-1/2 sm:w-[460px] sm:-translate-x-1/2 sm:-translate-y-1/2 sm:rounded-[28px]"
         role="dialog" aria-modal="true" aria-labelledby="lp-auth-title">
        <div class="flex items-start justify-between px-7 pt-7">
            <h2 id="lp-auth-title" class="lp-title text-[30px] text-grafito">
                <span x-show="mode === 'login'">Hola de <span class="lp-accent">nuevo</span></span>
                <span x-show="mode === 'register'" x-cloak>Crea tu <span class="lp-accent">cuenta</span></span>
            </h2>
            <button type="button" @click="open = false" class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-humo-300 text-grafito-500 transition hover:border-grafito hover:text-grafito" aria-label="Cerrar">
                <x-site.icon name="close" class="h-4 w-4" />
            </button>
        </div>

        <div class="px-7 pb-8 pt-6">
            <div x-show="mode === 'login'">
                <x-site.login-form />
                <p class="mt-5 text-center text-[15px] text-grafito-500">¿No tienes cuenta? <button type="button" class="ui-link" @click="mode = 'register'">Créala gratis</button></p>
            </div>
            <div x-show="mode === 'register'" x-cloak>
                <x-site.register-form />
                <p class="mt-5 text-center text-[15px] text-grafito-500">¿Ya tienes cuenta? <button type="button" class="ui-link" @click="mode = 'login'">Ingresa aquí</button></p>
            </div>
        </div>
    </div>
</div>
