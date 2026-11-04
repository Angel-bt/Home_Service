{{-- Estado vacío: qué significa y qué hacer ahora. Uso: <x-ui.empty icon="inbox" title="…">texto<x-slot:actions>…</x-slot:actions></x-ui.empty> --}}
@props(['icon' => 'inbox', 'title'])
<div {{ $attributes->merge(['class' => 'ui-card flex flex-col items-center px-6 py-14 text-center']) }}>
    <span class="grid h-14 w-14 place-items-center rounded-full bg-humo-100 text-grafito-500"><x-site.icon :name="$icon" class="h-7 w-7" /></span>
    <h3 class="lp-title mt-5 text-2xl">{{ $title }}</h3>
    @if (trim($slot) !== '')
        <p class="mt-2 max-w-md text-[15px] leading-relaxed text-grafito-500">{{ $slot }}</p>
    @endif
    @isset($actions)
        <div class="mt-7 flex flex-wrap justify-center gap-3">{{ $actions }}</div>
    @endisset
</div>
