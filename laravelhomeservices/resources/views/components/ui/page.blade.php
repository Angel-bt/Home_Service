{{-- Cabecera de páginas públicas internas. Uso: <x-ui.page title="Buscar" accent="servicios" eyebrow="Catálogo" lede="…">(filtros opcionales)</x-ui.page> --}}
@props(['title', 'accent' => null, 'eyebrow' => null, 'lede' => null, 'back' => null])
<section {{ $attributes->merge(['class' => 'border-b border-humo-300/70 pb-10 pt-10 lg:pb-14 lg:pt-14']) }}>
    <div class="mx-auto max-w-[84rem] px-5 sm:px-8">
        @if ($back)
            <a href="{{ $back[1] }}" class="mb-6 inline-flex items-center gap-2 text-[14px] text-grafito-500 hover:text-grafito"><x-site.icon name="arrow-left" class="h-4 w-4" /> {{ $back[0] }}</a>
        @endif
        @if ($eyebrow)
            <p class="lp-eyebrow uppercase tracking-[.14em] text-grafito-400">{{ $eyebrow }}</p>
        @endif
        <h1 class="lp-title mt-3 max-w-4xl text-[2.5rem] sm:text-5xl lg:text-6xl">{{ $title }}@if ($accent) <span class="lp-accent">{{ $accent }}</span>@endif</h1>
        @if ($lede)
            <p class="mt-5 max-w-2xl text-[17px] leading-relaxed text-grafito-500">{{ $lede }}</p>
        @endif
        @if (trim($slot) !== '')
            <div class="mt-8">{{ $slot }}</div>
        @endif
    </div>
</section>
