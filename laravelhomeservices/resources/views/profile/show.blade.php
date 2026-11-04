<x-app-layout>
    <x-ui.panel title="Cuenta y" accent="contraseña" help="Tus datos de acceso. Aquí puedes cambiar tu nombre, tu correo o tu contraseña.">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            @livewire('profile.update-profile-information-form')
            <x-section-border />
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            @livewire('profile.update-password-form')
            <x-section-border />
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            @livewire('profile.two-factor-authentication-form')
            <x-section-border />
        @endif

        @livewire('profile.logout-other-browser-sessions-form')

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <x-section-border />
            @livewire('profile.delete-user-form')
        @endif
    </x-ui.panel>
</x-app-layout>
