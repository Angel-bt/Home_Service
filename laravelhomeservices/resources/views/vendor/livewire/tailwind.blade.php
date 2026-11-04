{{-- Paginación de Livewire con el diseño del sitio: solo "Anterior / Siguiente" y en qué página estás (más claro que una fila de números). --}}
@php
    $scrollTo = $scrollTo ?? 'body';
    $scrollJs = $scrollTo !== false ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()" : '';
@endphp
<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Paginación" class="mt-8 flex items-center justify-between gap-4">
            @if ($paginator->onFirstPage())
                <span class="ui-btn ui-btn-secondary ui-btn-sm pointer-events-none opacity-40"><x-site.icon name="arrow-left" class="h-4 w-4" /> Anterior</span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollJs }}" wire:loading.attr="disabled" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="arrow-left" class="h-4 w-4" /> Anterior</button>
            @endif
            <p class="text-[14px] text-grafito-500">Página <span class="font-medium text-grafito">{{ $paginator->currentPage() }}</span>@if (method_exists($paginator, 'lastPage')) de {{ $paginator->lastPage() }}@endif</p>
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollJs }}" wire:loading.attr="disabled" class="ui-btn ui-btn-secondary ui-btn-sm">Siguiente <x-site.icon name="arrow-right" class="h-4 w-4" /></button>
            @else
                <span class="ui-btn ui-btn-secondary ui-btn-sm pointer-events-none opacity-40">Siguiente <x-site.icon name="arrow-right" class="h-4 w-4" /></span>
            @endif
        </nav>
    @endif
</div>
