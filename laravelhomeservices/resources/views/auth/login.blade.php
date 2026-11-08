<x-base-layout title="Ingresar">
    <x-ui.auth-card title="Hola de" accent="nuevo" lede="Ingresa con el correo y la contraseña de tu cuenta.">
        <x-site.login-form />
        <x-slot:footer>
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="ui-link">Créala gratis</a>
        </x-slot:footer>
    </x-ui.auth-card>
</x-base-layout>
