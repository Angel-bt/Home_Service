<div>
    @include('livewire.partials.page-header', ['title' => 'Pagos del plan Pro'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    @if (Session::has('message'))
                        <div class="alert alert-success">{{ Session::get('message') }}</div>
                    @endif
                    <div class="panel panel-default">
                        <div class="panel-heading">Registrar pago en efectivo recibido</div>
                        <div class="panel-body">
                            <form wire:submit.prevent="recordCashPayment" class="form-inline">
                                <select class="form-control" wire:model="cashUserId" aria-label="Proveedor">
                                    <option value="">Selecciona el proveedor</option>
                                    @foreach ($providers as $provider)
                                        <option value="{{ $provider->id }}">{{ $provider->name }} ({{ $provider->email }})</option>
                                    @endforeach
                                </select>
                                <select class="form-control" wire:model="cashPeriods" aria-label="Periodo">
                                    @foreach ($periodOptions as $option)
                                        <option value="{{ $option }}">{{ $option * $periodDays }} días — ${{ number_format($price * $option, 2) }}</option>
                                    @endforeach
                                </select>
                                <input type="text" class="form-control" wire:model="cashNote" placeholder="Nota (opcional), ej.: recibido en oficina">
                                <button type="submit" class="btn btn-success" onclick="confirm('¿Confirmas que recibiste el dinero? El plan Pro se activará de inmediato.') || event.stopImmediatePropagation()">Registrar y activar Pro</button>
                                @error('cashUserId') <div class="text-danger">{{ $message }}</div> @enderror
                                @error('cashPeriods') <div class="text-danger">{{ $message }}</div> @enderror
                                @error('cashNote') <div class="text-danger">{{ $message }}</div> @enderror
                            </form>
                            <small class="text-muted">Para avisos de efectivo que el proveedor ya registró, usa «Aprobar» en la lista de abajo.</small>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-md-6">Comprobantes y pagos ({{ $pendingCount }} en revisión)</div>
                                <div class="col-md-6">
                                    <select class="form-control pull-right" style="max-width: 220px;" wire:model.live="status">
                                        <option value="">Todos</option>
                                        @foreach (\App\Models\SubscriptionPayment::STATUS_LABELS as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            @if ($payments->isEmpty())
                                <p class="text-center">No hay pagos con este estado.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Proveedor</th>
                                                <th>Método</th>
                                                <th>Referencia</th>
                                                <th>Periodo</th>
                                                <th>Monto</th>
                                                <th>Comprobante</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payments as $payment)
                                                <tr wire:key="payment-{{ $payment->id }}">
                                                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        {{ $payment->user?->name }}<br>
                                                        <small>{{ $payment->user?->email }} · {{ $payment->user?->phone }}</small>
                                                        @if ($payment->user?->isPro() && $payment->user->plan_expires_at)
                                                            <br><small class="text-success">Pro hasta {{ $payment->user->plan_expires_at->format('d/m/Y') }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $payment->methodLabel() }}</td>
                                                    <td>{{ $payment->reference ?? $payment->gateway_transaction_id }}</td>
                                                    <td>{{ $payment->periods * $periodDays }} días</td>
                                                    <td>${{ number_format((float) $payment->amount, 2) }}</td>
                                                    <td>
                                                        @if ($payment->receipt_path)
                                                            <a href="{{ route('admin.payment_receipt', $payment) }}" target="_blank" rel="noopener"><i class="fa fa-file"></i> Ver</a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @include('livewire.partials.payment-status', ['payment' => $payment])
                                                        @if ($payment->reviewer)
                                                            <br><small>por {{ $payment->reviewer->name }}</small>
                                                        @endif
                                                        @if ($payment->status === 'rejected' && $payment->notes)
                                                            <br><small>{{ $payment->notes }}</small>
                                                        @endif
                                                    </td>
                                                    <td style="white-space: nowrap;">
                                                        @if ($payment->status === 'pending')
                                                            <button class="btn btn-xs btn-success" onclick="confirm('¿Confirmas que recibiste ${{ number_format((float) $payment->amount, 2) }}?') || event.stopImmediatePropagation()" wire:click="approve({{ $payment->id }})">Aprobar</button>
                                                            <button class="btn btn-xs btn-danger" wire:click="startReject({{ $payment->id }})">Rechazar</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if ($rejectingId === $payment->id)
                                                    <tr wire:key="reject-{{ $payment->id }}">
                                                        <td colspan="9">
                                                            <form wire:submit.prevent="reject" class="form-inline">
                                                                <input type="text" class="form-control" style="min-width: 360px;" wire:model="rejectReason" placeholder="Motivo (lo verá el proveedor), ej.: no encontramos la transferencia">
                                                                <button type="submit" class="btn btn-danger">Rechazar pago</button>
                                                                <button type="button" class="btn btn-link" wire:click="$set('rejectingId', null)">Cancelar</button>
                                                                @error('rejectReason') <div class="text-danger">{{ $message }}</div> @enderror
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $payments->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
