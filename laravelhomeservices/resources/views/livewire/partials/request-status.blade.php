@php
    $statusClasses = [
        'pending' => 'label-warning',
        'accepted' => 'label-info',
        'rejected' => 'label-default',
        'completed' => 'label-success',
        'cancelled' => 'label-default',
    ];
@endphp
<span class="label {{ $statusClasses[$request->status] ?? 'label-default' }}">{{ $request->statusLabel() }}</span>
