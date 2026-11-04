<x-base-layout>
    <x-ui.auth-card title="Confirma tu" accent="contraseña" lede="Por seguridad, escríbela de nuevo antes de continuar.">
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf
            <x-ui.field label="Contraseña" name="password">
                <input id="password" type="password" name="password" required autofocus autocomplete="current-password" class="ui-input @error('password') ui-input-invalid @enderror">
            </x-ui.field>
            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full">Continuar</button>
        </form>
    </x-ui.auth-card>
</x-base-layout>
