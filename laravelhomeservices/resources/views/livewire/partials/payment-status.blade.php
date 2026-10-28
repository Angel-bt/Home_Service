@php
    $paymentClasses = ['pending' => 'label-warning', 'approved' => 'label-success', 'rejected' => 'label-danger'];
@endphp
<span class="label {{ $paymentClasses[$payment->status] ?? 'label-default' }}">{{ $payment->statusLabel() }}</span>
