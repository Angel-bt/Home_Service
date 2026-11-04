<div>
    <x-ui.panel title="Pagos" accent="del plan Pro" help="Aquí activas el plan Pro de los proveedores. Primero confirma que el dinero llegó a la cuenta o que lo recibiste; después toca «Aprobar».">
        <div class="space-y-8">
            {{-- Filtro por estado --}}
            <div>
                <div class="-mx-5 mb-6 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar por estado">
                    @foreach (['pending' => 'En revisión', 'approved' => 'Aprobados', 'rejected' => 'Rechazados', '' => 'Todos'] as $value => $label)
                        <button type="button" wire:click="$set('status', '{{ $value }}')" @if ($status === $value) aria-pressed="true" @endif
                                class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $status === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                            {{ $label }}@if ($value === 'pending') <span class="{{ $status === $value ? 'text-white/60' : 'text-grafito-400' }}">{{ $pendingCount }}</span>@endif
                        </button>
                    @endforeach
                </div>

                @if ($payments->isEmpty())
                    <x-ui.empty icon="card" :title="$status === 'pending' ? 'No hay pagos por revisar' : 'No hay pagos con este estado'">
                        {{ $status === 'pending' ? 'Cuando un proveedor envíe un comprobante o avise un pago en efectivo, aparecerá aquí.' : 'Prueba con otro filtro.' }}
                    </x-ui.empty>
                @else
                    <div class="space-y-4">
                        @foreach ($payments as $payment)
                            <article wire:key="payment-{{ $payment->id }}" class="ui-card overflow-hidden {{ $payment->status === 'pending' ? '!border-oro-400' : '' }}">
                                <div class="flex flex-wrap items-start justify-between gap-3 p-6 pb-0">
                                    <div class="min-w-0">
                                        <h2 class="lp-title text-xl leading-snug">{{ $payment->user?->name ?? 'Proveedor eliminado' }}</h2>
                                        <p class="mt-1 break-words text-[13px] text-grafito-500">
                                            {{ $payment->user?->email }}@if ($payment->user?->phone) · {{ $payment->user->phone }}@endif
                                            @if ($payment->user?->isPro() && $payment->user->plan_expires_at) · Pro hasta {{ $payment->user->plan_expires_at->format('d/m/Y') }}@endif
                                        </p>
                                    </div>
                                    <x-ui.status :status="$payment->status" :label="$payment->statusLabel()" />
                                </div>

                                <dl class="grid grid-cols-2 gap-4 p-6 text-[15px] sm:grid-cols-4">
                                    <div><dt class="text-[13px] text-grafito-500">Monto</dt><dd class="mt-0.5 text-[20px] font-medium">${{ number_format((float) $payment->amount, 2) }}</dd></div>
                                    <div><dt class="text-[13px] text-grafito-500">Periodo</dt><dd class="mt-0.5 font-medium">{{ $payment->periods * $periodDays }} días</dd></div>
                                    <div><dt class="text-[13px] text-grafito-500">Cómo paga</dt><dd class="mt-0.5 font-medium">{{ $payment->methodLabel() }}</dd></div>
                                    <div><dt class="text-[13px] text-grafito-500">Fecha</dt><dd class="mt-0.5 font-medium">{{ $payment->created_at->format('d/m/Y H:i') }}</dd></div>
                                    @if ($payment->reference ?? $payment->gateway_transaction_id)
                                        <div class="col-span-2 sm:col-span-4"><dt class="text-[13px] text-grafito-500">Referencia o detalle</dt><dd class="mt-0.5 break-words">{{ $payment->reference ?? $payment->gateway_transaction_id }}</dd></div>
                                    @endif
                                    @if ($payment->status === 'rejected' && $payment->notes)
                                        <div class="col-span-2 sm:col-span-4"><dt class="text-[13px] text-grafito-500">Motivo del rechazo</dt><dd class="mt-0.5">{{ $payment->notes }}</dd></div>
                                    @endif
                                </dl>

                                @if ($payment->receipt_path || $payment->status === 'pending' || $payment->reviewer)
                                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-humo-200 bg-humo-50 px-6 py-4">
                                        <div class="text-[14px] text-grafito-600">
                                            @if ($payment->receipt_path)
                                                <a href="{{ route('admin.payment_receipt', $payment) }}" target="_blank" rel="noopener" class="ui-link inline-flex items-center gap-1.5"><x-site.icon name="eye" class="h-4 w-4" /> Ver comprobante</a>
                                            @elseif ($payment->status === 'pending')
                                                Pago en efectivo: apruébalo cuando tengas el dinero en la mano.
                                            @endif
                                            @if ($payment->reviewer)<span class="block text-[13px] text-grafito-500">Revisado por {{ $payment->reviewer->name }}</span>@endif
                                        </div>
                                        @if ($payment->status === 'pending' && $rejectingId !== $payment->id)
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" wire:click="approve({{ $payment->id }})" wire:confirm="¿Confirmas que recibiste ${{ number_format((float) $payment->amount, 2) }}? El plan Pro se activará de inmediato." class="ui-btn ui-btn-success ui-btn-sm"><x-site.icon name="check" class="h-4 w-4" /> Aprobar y activar Pro</button>
                                                <button type="button" wire:click="startReject({{ $payment->id }})" class="ui-btn ui-btn-danger ui-btn-sm">Rechazar</button>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if ($rejectingId === $payment->id)
                                    <form wire:submit="reject" class="space-y-4 border-t border-humo-200 p-6">
                                        <x-ui.field label="¿Por qué lo rechazas?" name="rejectReason" for="reject-{{ $payment->id }}" hint="El proveedor verá este motivo. Dile qué debe corregir.">
                                            <input type="text" id="reject-{{ $payment->id }}" class="ui-input @error('rejectReason') ui-input-invalid @enderror" wire:model="rejectReason" placeholder="Ej.: no encontramos la transferencia en la cuenta">
                                        </x-ui.field>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">Rechazar pago</button>
                                            <button type="button" wire:click="$set('rejectingId', null)" class="ui-btn ui-btn-ghost ui-btn-sm">No rechazar</button>
                                        </div>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    {{ $payments->links() }}
                @endif
            </div>

            {{-- Efectivo recibido sin aviso previo --}}
            <section class="ui-card ui-card-pad">
                <h2 class="lp-title text-2xl">¿Recibiste efectivo en persona?</h2>
                <p class="mt-1 max-w-2xl text-[15px] text-grafito-500">Regístralo aquí si el proveedor te pagó sin avisar por la página. Si ya aparece en la lista de arriba, usa «Aprobar» en vez de esto, para no contarlo dos veces.</p>
                <form wire:submit="recordCashPayment" wire:confirm="¿Confirmas que recibiste el dinero? El plan Pro se activará de inmediato." class="mt-6 grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Proveedor" name="cashUserId" for="cash-user">
                        <select id="cash-user" class="ui-input @error('cashUserId') ui-input-invalid @enderror" wire:model="cashUserId">
                            <option value="">Elige el proveedor</option>
                            @foreach ($providers as $provider)
                                <option value="{{ $provider->id }}">{{ $provider->name }} ({{ $provider->email }})</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Periodo que pagó" name="cashPeriods" for="cash-periods">
                        <select id="cash-periods" class="ui-input @error('cashPeriods') ui-input-invalid @enderror" wire:model="cashPeriods">
                            @foreach ($periodOptions as $option)
                                <option value="{{ $option }}">{{ $option * $periodDays }} días · ${{ number_format($price * $option, 2) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Nota" name="cashNote" for="cash-note" class="sm:col-span-2" optional>
                        <input type="text" id="cash-note" class="ui-input @error('cashNote') ui-input-invalid @enderror" wire:model="cashNote" placeholder="Ej.: recibido en la oficina">
                    </x-ui.field>
                    <div class="sm:col-span-2">
                        <button type="submit" class="ui-btn ui-btn-primary">Registrar pago y activar Pro</button>
                    </div>
                </form>
            </section>
        </div>
    </x-ui.panel>
</div>
