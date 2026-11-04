<x-base-layout>
    <x-ui.auth-card title="Crea tu" accent="cuenta" lede="Es gratis y toma un minuto." wide>
        <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ as: '{{ old('registeras', request('como') === 'SVP' ? 'SVP' : 'CST') }}', sending: false }" @submit="sending = true">
            @csrf
            <fieldset>
                <legend class="ui-label">¿Para qué quieres tu cuenta?</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ([['CST', 'Busco servicios', 'Quiero contratar a alguien.'], ['SVP', 'Ofrezco servicios', 'Tengo un oficio y busco clientes.']] as [$value, $optionTitle, $hint])
                        <label class="cursor-pointer rounded-2xl border p-4 transition" :class="as === '{{ $value }}' ? 'border-noche-900 bg-humo-50 ring-1 ring-noche-900' : 'border-humo-300 hover:border-grafito-400'">
                            <input type="radio" name="registeras" value="{{ $value }}" x-model="as" class="sr-only">
                            <span class="block text-[15px] font-medium">{{ $optionTitle }}</span>
                            <span class="mt-0.5 block text-[13px] text-grafito-500">{{ $hint }}</span>
                        </label>
                    @endforeach
                </div>
                @error('registeras')
                    <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                @enderror
            </fieldset>
            <x-ui.field label="Tu nombre completo" name="name">
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="ui-input @error('name') ui-input-invalid @enderror">
            </x-ui.field>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Tu correo" name="email">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="ui-input @error('email') ui-input-invalid @enderror">
                </x-ui.field>
                <x-ui.field label="Tu celular" name="phone" hint="Ej.: 0991234567">
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" class="ui-input @error('phone') ui-input-invalid @enderror">
                </x-ui.field>
                <x-ui.field label="Crea una contraseña" name="password" hint="Mínimo 8 letras o números.">
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="ui-input @error('password') ui-input-invalid @enderror">
                </x-ui.field>
                <x-ui.field label="Repite la contraseña" for="password_confirmation">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="ui-input">
                </x-ui.field>
            </div>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full" :disabled="sending">
                <span x-show="!sending">Crear mi cuenta gratis</span>
                <span x-show="sending" x-cloak>Creando tu cuenta…</span>
            </button>
            <p class="text-center text-[13px] text-grafito-500">Al crear tu cuenta aceptas los <a href="{{ route('home.terms') }}" class="ui-link">términos</a> y la <a href="{{ route('home.privacy') }}" class="ui-link">privacidad</a>.</p>
        </form>
        <x-slot:footer>
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="ui-link">Ingresa aquí</a>
        </x-slot:footer>
    </x-ui.auth-card>
</x-base-layout>
