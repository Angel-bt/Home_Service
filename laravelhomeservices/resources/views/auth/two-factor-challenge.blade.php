<x-base-layout>
    <div x-data="{ recovery: false }">
        <x-ui.auth-card title="Un paso" accent="más" lede="Tu cuenta tiene verificación en dos pasos.">
            <p class="mb-5 text-[15px] text-grafito-600" x-show="! recovery">Escribe el código de 6 números que aparece en tu app de autenticación.</p>
            <p class="mb-5 text-[15px] text-grafito-600" x-cloak x-show="recovery">Escribe uno de los códigos de recuperación que guardaste al activar esta opción.</p>
            <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
                @csrf
                <div x-show="! recovery">
                    <x-ui.field label="Código" name="code">
                        <input id="code" type="text" inputmode="numeric" name="code" autofocus x-ref="code" autocomplete="one-time-code" class="ui-input @error('code') ui-input-invalid @enderror">
                    </x-ui.field>
                </div>
                <div x-cloak x-show="recovery">
                    <x-ui.field label="Código de recuperación" name="recovery_code">
                        <input id="recovery_code" type="text" name="recovery_code" x-ref="recovery_code" autocomplete="one-time-code" class="ui-input @error('recovery_code') ui-input-invalid @enderror">
                    </x-ui.field>
                </div>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full">Ingresar</button>
                <button type="button" class="ui-link w-full text-center text-[14px]" x-show="! recovery" x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">No tengo el código: usar un código de recuperación</button>
                <button type="button" class="ui-link w-full text-center text-[14px]" x-cloak x-show="recovery" x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">Usar el código de la app</button>
            </form>
        </x-ui.auth-card>
    </div>
</x-base-layout>
