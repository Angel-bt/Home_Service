{{-- Campo con etiqueta, error y ayuda en lenguaje simple. Uso:
     <x-ui.field label="WhatsApp" name="whatsapp" hint="Los clientes te escriben aquí.">
         <input id="whatsapp" class="ui-input @error('whatsapp') ui-input-invalid @enderror" wire:model="whatsapp">
     </x-ui.field> --}}
@props(['label', 'name' => null, 'for' => null, 'hint' => null, 'optional' => false])
<div {{ $attributes }}>
    <label for="{{ $for ?? $name }}" class="ui-label">
        {{ $label }}@if ($optional) <span class="font-normal text-grafito-400">(opcional)</span>@endif
    </label>
    {{ $slot }}
    @if ($name)
        @error($name)
            <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
        @enderror
    @endif
    @if ($hint)
        <p class="ui-hint">{{ $hint }}</p>
    @endif
</div>
