<div>
    <x-ui.panel title="Mi plan" :help="$isPro ? 'Tienes el plan Pro: publicas servicios sin límite.' : 'Con el plan gratuito publicas hasta ' . $limit . ' servicios. Pásate a Pro si necesitas más.'">
        <div class="space-y-6">
            <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">
                {{-- Plan actual --}}
                <section class="ui-card ui-card-pad self-start">
                    <p class="text-[13px] font-medium uppercase tracking-[.08em] text-grafito-400">Tu plan actual</p>
                    <p class="lp-title mt-3 text-4xl">{{ $isPro ? 'Pro' : 'Gratis' }}</p>
                    @if ($isPro)
                        <p class="mt-3 text-[15px] text-grafito-600">Publicas servicios <strong class="font-medium">sin límite</strong>.</p>
                        @if ($user->plan_expires_at)
                            <p class="mt-2 text-[15px] text-grafito-600">Vigente hasta el <strong class="font-medium">{{ $user->plan_expires_at->format('d/m/Y') }}</strong> ({{ $user->plan_expires_at->diffForHumans() }}).</p>
                            <p class="ui-hint">Puedes renovar antes: los días se suman a tu fecha actual.</p>
                        @endif
                    @else
                        <p class="mt-3 text-[15px] text-grafito-600">Llevas <strong class="font-medium">{{ $used }} de {{ $limit }}</strong> servicios.</p>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-humo-200" aria-hidden="true">
                            <div class="h-full rounded-full bg-noche-900" style="width: {{ $limit > 0 ? min(100, round($used / $limit * 100)) : 0 }}%"></div>
                        </div>
                        @if ($user->plan_expires_at)
                            <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> Tu plan Pro venció el {{ $user->plan_expires_at->format('d/m/Y') }}. Renuévalo para volver a publicar sin límite.</p>
                        @endif
                    @endif

                    <div class="mt-8 border-t border-humo-200 pt-6">
                        <p class="text-[17px] font-medium">Plan Pro · ${{ number_format($price, 2) }} cada {{ $periodDays }} días</p>
                        <ul class="mt-4 space-y-3 text-[15px] text-grafito-600">
                            @foreach (['Servicios publicados sin límite', 'Más oportunidades de que te encuentren', 'El cliente te sigue pagando directo a ti'] as $benefit)
                                <li class="flex gap-3"><x-site.icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" /> {{ $benefit }}</li>
                            @endforeach
                        </ul>
                    </div>
                </section>

                {{-- Activar o renovar --}}
                <section class="ui-card ui-card-pad">
                    <h2 class="lp-title text-2xl">{{ $isPro ? 'Renovar' : 'Activar' }} Plan Pro</h2>

                    @if ($hasPending)
                        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-[15px] text-sky-950" role="status">
                            <x-site.icon name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-sky-700" />
                            <div>
                                <p class="font-medium">Tu pago está en revisión.</p>
                                <p class="mt-1">No tienes que hacer nada más. Activaremos tu plan apenas lo confirmemos, normalmente el mismo día. Abajo puedes ver cómo va.</p>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 space-y-5">
                            <x-ui.field label="1. ¿Por cuánto tiempo?" name="periods" for="periods">
                                <select id="periods" class="ui-input @error('periods') ui-input-invalid @enderror" wire:model.live="periods">
                                    @foreach ($periodOptions as $option)
                                        <option value="{{ $option }}">{{ $option * $periodDays }} días · ${{ number_format($price * $option, 2) }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>

                            @if ($payphoneEnabled)
                                {{-- Pago con tarjeta: desactivado por defecto (PAYPHONE_ENABLED). --}}
                                <form method="POST" action="{{ route('billing.payphone.start') }}">
                                    @csrf
                                    <input type="hidden" name="periods" value="{{ $periods }}">
                                    <button type="submit" class="ui-btn ui-btn-primary w-full"><x-site.icon name="card" class="h-4 w-4" /> Pagar ${{ number_format($total, 2) }} con tarjeta</button>
                                </form>
                                <p class="text-center text-[14px] text-grafito-500">o paga por transferencia o en efectivo</p>
                            @endif

                            <fieldset>
                                <legend class="ui-label">2. ¿Cómo vas a pagar los ${{ number_format($total, 2) }}?</legend>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach (['transfer' => ['Transferencia', 'card'], 'cash' => ['Efectivo', 'cash']] as $value => [$label, $icon])
                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-humo-300 bg-white px-4 py-3 text-[15px] transition has-[:checked]:border-noche-900 has-[:checked]:bg-humo-50">
                                            <input type="radio" class="text-noche-900 focus:ring-noche-600/20" name="method" value="{{ $value }}" wire:model.live="method">
                                            <x-site.icon :name="$icon" class="h-5 w-5 text-grafito-500" /> {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @error('method')
                                    <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                                @enderror
                            </fieldset>

                            @if ($method === 'cash')
                                <div class="rounded-2xl bg-humo-100 p-5 text-[15px] leading-relaxed text-grafito-600">
                                    <p class="font-medium text-grafito">3. Entrega el dinero en persona</p>
                                    <p class="mt-1">Coordina el lugar y la hora con la administración de Malakplace por WhatsApp. Activamos tu plan cuando recibimos el pago.</p>
                                    @if ($contactWhatsapp)
                                        <a href="{{ $contactWhatsapp }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm mt-3"><x-site.icon name="whatsapp" class="h-4 w-4" /> {{ config('marketplace.contact_phone') }}</a>
                                    @endif
                                </div>
                                <form wire:submit="submitReceipt" class="space-y-5">
                                    <x-ui.field label="¿Cuándo y dónde entregarás el pago?" name="reference" for="reference" optional>
                                        <input type="text" id="reference" class="ui-input @error('reference') ui-input-invalid @enderror" wire:model="reference" placeholder="Ej.: viernes en el parque central de Malacatos">
                                    </x-ui.field>
                                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" wire:loading.attr="disabled" wire:target="submitReceipt">Avisar que pagaré en efectivo</button>
                                </form>
                            @else
                                <div class="rounded-2xl bg-humo-100 p-5 text-[15px] leading-relaxed text-grafito-600">
                                    <p class="font-medium text-grafito">3. Transfiere ${{ number_format($total, 2) }} a esta cuenta</p>
                                    @if ($bank['account_number'])
                                        <p class="mt-1">{{ $bank['bank'] }} · Cuenta de {{ mb_strtolower($bank['account_type']) }} <strong class="font-medium text-grafito">{{ $bank['account_number'] }}</strong></p>
                                        <p>A nombre de {{ $bank['holder'] }}@if ($bank['holder_id']) · C.I./RUC {{ $bank['holder_id'] }}@endif</p>
                                    @else
                                        <p class="mt-1">Escríbenos por WhatsApp y te pasamos los datos de la cuenta.</p>
                                        @if ($transferWhatsapp = \App\Support\WhatsApp::link(config('marketplace.contact_phone'), 'Hola, quiero pagar el plan Pro por transferencia. ¿Me pasan los datos de la cuenta? Soy ' . $user->name . '.'))
                                            <a href="{{ $transferWhatsapp }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm mt-3"><x-site.icon name="whatsapp" class="h-4 w-4" /> {{ config('marketplace.contact_phone') }}</a>
                                        @endif
                                    @endif
                                </div>
                                <form wire:submit="submitReceipt" class="space-y-5">
                                    <p class="ui-label !mb-0">4. Envíanos el comprobante</p>
                                    <x-ui.field label="Número de comprobante" name="reference" for="reference" hint="Aparece en el recibo de tu banco.">
                                        <input type="text" id="reference" class="ui-input @error('reference') ui-input-invalid @enderror" wire:model="reference">
                                    </x-ui.field>
                                    <x-ui.field label="Foto o PDF del comprobante" name="receipt" for="receipt" hint="JPG, PNG, WebP o PDF de hasta 5 MB. Una captura de pantalla sirve.">
                                        <input type="file" id="receipt" accept="image/*,application/pdf" class="ui-file" wire:model="receipt">
                                        <p wire:loading wire:target="receipt" class="ui-hint">Subiendo archivo…</p>
                                    </x-ui.field>
                                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg" wire:loading.attr="disabled" wire:target="submitReceipt, receipt">Enviar comprobante</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </section>
            </div>

            <section>
                <h2 class="lp-title mb-4 text-2xl">Historial de pagos</h2>
                @if ($payments->isEmpty())
                    <x-ui.empty icon="card" title="Aún no tienes pagos">Cuando envíes un comprobante o avises un pago, aparecerá aquí con su estado.</x-ui.empty>
                @else
                    <ul class="ui-card divide-y divide-humo-200">
                        @foreach ($payments as $payment)
                            <li class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                                <div class="min-w-0">
                                    <p class="text-[15px] font-medium">${{ number_format((float) $payment->amount, 2) }} · {{ $payment->periods * $periodDays }} días · {{ $payment->methodLabel() }}</p>
                                    <p class="mt-0.5 text-[13px] text-grafito-500">
                                        {{ $payment->created_at->format('d/m/Y') }}
                                        @if ($payment->status === 'approved' && $payment->paid_until) · Pro hasta {{ $payment->paid_until->format('d/m/Y') }}@endif
                                    </p>
                                    @if ($payment->status === 'rejected' && $payment->notes)
                                        <p class="mt-1 text-[14px] text-red-800">Motivo: {{ $payment->notes }}. Corrígelo y vuelve a enviar, o escríbenos.</p>
                                    @endif
                                </div>
                                <x-ui.status :status="$payment->status" :label="$payment->statusLabel()" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </x-ui.panel>
</div>
