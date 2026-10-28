<div>
    @include('livewire.partials.page-header', ['title' => 'Mi plan'])
    <section class="content-central">
        <div class="content_info">
            <div class="paddings-mini">
                <div class="container">
                    @if (Session::has('message'))
                        <div class="alert alert-success">{{ Session::get('message') }}</div>
                    @endif
                    @if (Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-5">
                            <div class="panel panel-default">
                                <div class="panel-heading">Tu plan actual</div>
                                <div class="panel-body">
                                    @if ($isPro)
                                        <h3 style="margin-top: 0;"><span class="label label-success">PRO</span></h3>
                                        <p>Publicas servicios <b>sin límite</b>.</p>
                                        @if ($user->plan_expires_at)
                                            <p>Vigente hasta el <b>{{ $user->plan_expires_at->format('d/m/Y') }}</b>
                                                ({{ $user->plan_expires_at->diffForHumans() }}).</p>
                                            <p class="text-muted">Puedes renovar antes: los días se suman a tu fecha actual.</p>
                                        @endif
                                    @else
                                        <h3 style="margin-top: 0;"><span class="label label-default">GRATIS</span></h3>
                                        <p>Puedes publicar hasta <b>{{ $limit }}</b> servicios. Usas <b>{{ $used }}</b>.</p>
                                        @if ($user->plan_expires_at)
                                            <p class="text-danger">Tu plan Pro venció el {{ $user->plan_expires_at->format('d/m/Y') }}.</p>
                                        @endif
                                    @endif
                                    <hr>
                                    <h4>Plan Pro · ${{ number_format($price, 2) }} cada {{ $periodDays }} días</h4>
                                    <ul class="list-styles">
                                        <li><i class="fa fa-check"></i> Servicios publicados sin límite</li>
                                        <li><i class="fa fa-check"></i> Más clientes de todo el valle</li>
                                        <li><i class="fa fa-check"></i> Sin comisiones por trabajo: el cliente te paga directo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="panel panel-default">
                                <div class="panel-heading">Activar o renovar Pro</div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label for="periods">Periodo</label>
                                        <select id="periods" class="form-control" wire:model.live="periods">
                                            @foreach ($periodOptions as $option)
                                                <option value="{{ $option }}">{{ $option * $periodDays }} días — ${{ number_format($price * $option, 2) }}</option>
                                            @endforeach
                                        </select>
                                        @error('periods') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    @if ($payphoneEnabled)
                                        <form method="POST" action="{{ route('billing.payphone.start') }}" style="margin-bottom: 20px;">
                                            @csrf
                                            <input type="hidden" name="periods" value="{{ $periods }}">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fa fa-credit-card"></i> Pagar ${{ number_format($total, 2) }} con tarjeta (Payphone)
                                            </button>
                                            <small class="text-muted">Se activa al instante.</small>
                                        </form>
                                        <p class="text-center text-muted">— o paga por transferencia / DeUna —</p>
                                    @endif

                                    <h4>1. Paga ${{ number_format($total, 2) }}</h4>
                                    @if ($bank['account_number'])
                                        <p>
                                            <b>Transferencia:</b> {{ $bank['bank'] }} · Cuenta de {{ mb_strtolower($bank['account_type']) }}
                                            <b>{{ $bank['account_number'] }}</b><br>
                                            A nombre de {{ $bank['holder'] }}@if ($bank['holder_id']) · C.I./RUC {{ $bank['holder_id'] }}@endif
                                        </p>
                                    @endif
                                    @if ($deunaPhone)
                                        <p><b>DeUna:</b> envía el pago al número <b>{{ $deunaPhone }}</b>.</p>
                                    @endif
                                    @if (! $bank['account_number'] && ! $deunaPhone)
                                        <p class="text-muted">Escríbenos por WhatsApp al {{ config('marketplace.contact_phone') }} para recibir los datos de pago.</p>
                                    @endif

                                    <h4>2. Sube tu comprobante</h4>
                                    @if ($hasPending)
                                        <div class="alert alert-info">Tienes un pago en revisión. Te avisaremos al activarlo.</div>
                                    @else
                                        <form wire:submit.prevent="submitReceipt">
                                            <div class="form-group">
                                                <label for="method">Método</label>
                                                <select id="method" class="form-control" wire:model="method">
                                                    <option value="transfer">Transferencia bancaria</option>
                                                    <option value="deuna">DeUna</option>
                                                </select>
                                                @error('method') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="reference">Número de comprobante / referencia</label>
                                                <input type="text" id="reference" class="form-control" wire:model="reference">
                                                @error('reference') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="form-group">
                                                <label for="receipt">Foto o PDF del comprobante</label>
                                                <input type="file" id="receipt" accept="image/*,application/pdf" class="form-control-file" wire:model="receipt">
                                                <div wire:loading wire:target="receipt" class="text-muted">Subiendo…</div>
                                                @error('receipt') <div class="text-danger">{{ $message }}</div> @enderror
                                            </div>
                                            <button type="submit" class="btn btn-success" wire:loading.attr="disabled">Enviar comprobante</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">Historial de pagos</div>
                        <div class="panel-body">
                            @if ($payments->isEmpty())
                                <p class="text-muted">Aún no tienes pagos.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr><th>Fecha</th><th>Método</th><th>Periodo</th><th>Monto</th><th>Estado</th><th>Detalle</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                                                    <td>{{ $payment->methodLabel() }}</td>
                                                    <td>{{ $payment->periods * $periodDays }} días</td>
                                                    <td>${{ number_format((float) $payment->amount, 2) }}</td>
                                                    <td>@include('livewire.partials.payment-status', ['payment' => $payment])</td>
                                                    <td>
                                                        @if ($payment->status === 'approved' && $payment->paid_until)
                                                            Pro hasta {{ $payment->paid_until->format('d/m/Y') }}
                                                        @elseif ($payment->status === 'rejected')
                                                            {{ $payment->notes }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
