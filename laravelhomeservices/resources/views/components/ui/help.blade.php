{{-- "¿Cómo funciona esta página?": explicación plegable (sin JavaScript, con <details>).
     Uso: <x-ui.help><x-ui.help-step>Primer paso</x-ui.help-step>…</x-ui.help> --}}
@props(['title' => '¿Cómo funciona esta página?', 'open' => false])
<details {{ $attributes->merge(['class' => 'group rounded-2xl border border-humo-300/70 bg-humo-50']) }} @if ($open) open @endif>
    <summary class="flex cursor-pointer list-none items-center gap-3 px-5 py-4 text-[15px] font-medium [&::-webkit-details-marker]:hidden">
        <x-site.icon name="question" class="h-5 w-5 text-grafito-500" />
        <span class="flex-1">{{ $title }}</span>
        <x-site.icon name="chevron-down" class="h-4 w-4 text-grafito-400 transition group-open:rotate-180" />
    </summary>
    <ol class="space-y-3 px-5 pb-5 text-[15px] leading-relaxed text-grafito-600">
        {{ $slot }}
    </ol>
</details>
