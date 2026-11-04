{{-- Cifra del panel. Con href, toda la tarjeta es un enlace; tone="alert" la resalta (algo pendiente). --}}
@props(['label', 'value', 'hint' => null, 'href' => null, 'tone' => 'default'])
@php
    $tag = $href ? 'a' : 'div';
    $classes = 'ui-card group block p-6 transition' . ($href ? ' hover:border-grafito-400' : '') . ($tone === 'alert' ? ' !border-amber-300 !bg-amber-50' : '');
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    <p class="text-[13px] font-medium text-grafito-500">{{ $label }}</p>
    <p class="lp-title mt-3 text-4xl">{{ $value }}</p>
    @if ($hint)
        <p class="mt-2 flex items-center gap-1 text-[13px] text-grafito-500">{{ $hint }}@if ($href) <x-site.icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />@endif</p>
    @endif
</{{ $tag }}>
