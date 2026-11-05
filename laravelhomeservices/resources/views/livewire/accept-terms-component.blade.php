<div>
    <x-ui.auth-card title="Actualizamos" accent="los términos" wide lede="Léelos y acéptalos para seguir usando tu cuenta. Es una sola vez.">
        <form wire:submit="accept" class="space-y-5" x-data="{ as: @js(auth()->user()->utype) }">
            <x-site.terms-summary />

            <label class="flex cursor-pointer items-start gap-3 text-[15px] leading-snug">
                <input type="checkbox" wire:model="terms" class="mt-0.5 h-5 w-5 shrink-0">
                <span>Leí y acepto los <a href="{{ route('home.terms') }}" target="_blank" class="ui-link">términos de uso</a> y la <a href="{{ route('home.privacy') }}" target="_blank" class="ui-link">política de privacidad</a>.</span>
            </label>
            @error('terms')
                <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
            @enderror

            @if (auth()->user()->utype === 'SVP')
                <label class="flex cursor-pointer items-start gap-3 text-[15px] leading-snug">
                    <input type="checkbox" wire:model="publication" class="mt-0.5 h-5 w-5 shrink-0">
                    <span><x-site.publication-consent /></span>
                </label>
                @error('publication')
                    <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                @enderror
            @endif

            <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg w-full">Acepto y quiero seguir</button>
        </form>

        <x-slot:footer>
            ¿No estás de acuerdo? <a href="{{ route('profile.show') }}" class="ui-link">Puedes eliminar tu cuenta</a>.
        </x-slot:footer>
    </x-ui.auth-card>
</div>
