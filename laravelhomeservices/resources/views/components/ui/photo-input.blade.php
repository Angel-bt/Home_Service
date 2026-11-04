{{-- Selector de foto: muestra la foto, el avance y los errores, y la achica antes de subirla (ver photo-script).
     model: propiedad Livewire; thumb: propiedad para la versión pequeña; current: URL de la foto actual; pdf: acepta PDF. --}}
@props(['id', 'model', 'thumb' => null, 'current' => null, 'pdf' => false, 'maxSide' => 1600, 'empty' => 'Toca para elegir una foto', 'shape' => 'wide'])
<div x-data="uiPhoto({ model: @js($model), thumb: @js($thumb), allowPdf: @js((bool) $pdf), maxSide: {{ (int) $maxSide }} })" wire:ignore.self {{ $attributes }}>
    <input type="file" id="{{ $id }}" class="sr-only" accept="{{ $pdf ? 'image/*,application/pdf' : 'image/*' }}" @change="pick($event)">
    <label for="{{ $id }}" class="group block cursor-pointer overflow-hidden rounded-2xl border border-dashed border-humo-300 bg-humo-50 transition hover:border-grafito-400 {{ $shape === 'round' ? 'max-w-[220px]' : '' }}">
        <div class="relative grid {{ $shape === 'round' ? 'aspect-square' : 'aspect-[16/9]' }} place-items-center">
            <img x-show="preview" x-cloak :src="preview" alt="Foto elegida" class="absolute inset-0 h-full w-full object-cover">
            @if ($current)
                <img x-show="!preview" src="{{ $current }}" alt="Foto actual" class="absolute inset-0 h-full w-full object-cover">
            @endif
            <div x-show="!preview" class="relative flex flex-col items-center gap-2 px-4 py-8 text-center {{ $current ? 'rounded-2xl bg-white/90 px-5 py-3' : '' }}">
                <x-site.icon name="{{ $pdf ? 'upload' : 'image' }}" class="h-7 w-7 text-grafito-400" />
                <span class="text-[15px] font-medium" x-text="fileName || @js($current ? 'Toca para cambiarla' : $empty)"></span>
            </div>
            <div x-cloak x-show="status === 'working' || status === 'uploading'" class="absolute inset-x-3 bottom-3 rounded-xl bg-noche-900/90 px-4 py-3 text-[13px] text-white">
                <span x-text="status === 'working' ? 'Preparando la foto…' : 'Subiendo… ' + progress + '%'"></span>
                <span class="mt-2 block h-1.5 overflow-hidden rounded-full bg-white/20"><span class="block h-full rounded-full bg-oro-400 transition-all" :style="`width: ${status === 'working' ? 5 : progress}%`"></span></span>
            </div>
        </div>
    </label>
    <p x-cloak x-show="status === 'done'" class="mt-2 flex items-center gap-1.5 text-[13px] font-medium text-emerald-800"><x-site.icon name="check-circle" class="h-4 w-4" /> Lista. Puedes cambiarla tocando otra vez.</p>
    <p x-cloak x-show="status === 'error'" class="ui-error" role="alert"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> <span x-text="message"></span></p>
</div>
