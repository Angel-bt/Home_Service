{{-- Estado de una solicitud o un pago, con un color fijo por estado. Uso: <x-ui.status :status="$request->status" :label="$request->statusLabel()" /> --}}
@props(['status', 'label'])
@php
    $classes = [
        'pending' => 'bg-amber-100 text-amber-900',
        'initiated' => 'bg-humo-200 text-grafito-600',
        'accepted' => 'bg-sky-100 text-sky-900',
        'completed' => 'bg-emerald-100 text-emerald-900',
        'approved' => 'bg-emerald-100 text-emerald-900',
        'rejected' => 'bg-red-100 text-red-900',
        'cancelled' => 'bg-humo-200 text-grafito-500',
    ][$status] ?? 'bg-humo-200 text-grafito-600';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center rounded-full px-3 py-1 text-[12px] font-medium {$classes}"]) }}>{{ $label }}</span>
