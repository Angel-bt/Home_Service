<x-base-layout>
    <x-ui.auth-card title="Crea tu" accent="cuenta" lede="Es gratis y toma un minuto.">
        <x-site.register-form :as="request('como') === 'SVP' ? 'SVP' : 'CST'" />
        <x-slot:footer>
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="ui-link">Ingresa aquí</a>
        </x-slot:footer>
    </x-ui.auth-card>
</x-base-layout>
