<x-base-layout>
    <x-ui.auth-card title="Crea una contraseña" accent="nueva" lede="Elige una que recuerdes. Después podrás ingresar con ella.">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-ui.field label="Tu correo" name="email">
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="email" class="ui-input @error('email') ui-input-invalid @enderror">
            </x-ui.field>
            <x-ui.field label="Contraseña nueva" name="password" hint="Mínimo 8 letras o números.">
                <input id="password" type="password" name="password" required autofocus autocomplete="new-password" class="ui-input @error('password') ui-input-invalid @enderror">
            </x-ui.field>
            <x-ui.field label="Repite la contraseña" for="password_confirmation">
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="ui-input">
            </x-ui.field>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full">Guardar contraseña</button>
        </form>
        <x-slot:footer>
            <a href="{{ route('login') }}" class="ui-link">Volver a ingresar</a>
        </x-slot:footer>
    </x-ui.auth-card>
</x-base-layout>
