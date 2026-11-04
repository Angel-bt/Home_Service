{{-- Solicitudes: la misma pantalla para proveedor y administración. Cada tarjeta explica en qué punto está y qué toca hacer. --}}
<div>
    <x-ui.panel title="Solicitudes" :help="$isAdmin ? 'Todas las solicitudes de la plataforma. Solo intervén si un cliente reclama o si el servicio es de la administración.' : 'Aquí llegan los clientes que quieren tus servicios. Respóndeles pronto: quien contesta primero suele quedarse con el trabajo.'">
        @unless ($isAdmin)
            <x-ui.help class="mb-6">
                <x-ui.help-step n="1">Llega una solicitud. Escríbele al cliente por WhatsApp para acordar el día y el precio.</x-ui.help-step>
                <x-ui.help-step n="2">Si puedes hacerlo, toca <strong>Aceptar</strong>. Si no, toca <strong>Rechazar</strong> para que el cliente busque a otro.</x-ui.help-step>
                <x-ui.help-step n="3">Cuando termines el trabajo, toca <strong>Ya terminé</strong>. Así el cliente puede dejarte una reseña, y las reseñas te traen más clientes.</x-ui.help-step>
            </x-ui.help>
        @endunless

        {{-- Filtro por estado --}}
        <div class="-mx-5 mb-6 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar por estado">
            @foreach (['' => 'Todas'] + \App\Models\ServiceRequest::LABELS as $value => $label)
                @php
                    $count = $value === '' ? $statusCounts->sum() : (int) ($statusCounts[$value] ?? 0);
                    $selected = $status === $value;
                @endphp
                <button type="button" wire:click="$set('status', '{{ $value }}')" @if ($selected) aria-pressed="true" @endif
                        class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $selected ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                    {{ $label }} <span class="{{ $selected ? 'text-white/60' : 'text-grafito-400' }}">{{ $count }}</span>
                </button>
            @endforeach
        </div>

        @if ($requests->isEmpty())
            @if ($status !== '')
                <x-ui.empty icon="inbox" title="No hay solicitudes con este estado">
                    Prueba con otro filtro para ver las demás.
                    <x-slot:actions>
                        <button type="button" wire:click="$set('status', '')" class="ui-btn ui-btn-secondary">Ver todas</button>
                    </x-slot:actions>
                </x-ui.empty>
            @elseif ($isAdmin)
                <x-ui.empty icon="inbox" title="Todavía no hay solicitudes">Cuando un cliente pida un servicio, aparecerá aquí.</x-ui.empty>
            @else
                <x-ui.empty icon="inbox" title="Todavía no te llegan solicitudes">
                    Para recibirlas, ten al menos un servicio publicado con buena foto, y tu WhatsApp y zonas al día.
                    <x-slot:actions>
                        <a href="{{ route('sprovider.services') }}" class="ui-btn ui-btn-primary">Ver mis servicios</a>
                        <a href="{{ route('sprovider.edit_profile') }}" class="ui-btn ui-btn-secondary">Revisar mi perfil</a>
                    </x-slot:actions>
                </x-ui.empty>
            @endif
        @else
            <div class="space-y-4">
                @foreach ($requests as $request)
                    @php
                        $wa = \App\Support\WhatsApp::link($request->phone, 'Hola ' . $request->name . ', te escribo por tu solicitud de "' . ($request->service?->name ?? 'servicio') . '" en Malakplace.');
                        $canAccept = $request->canTransitionTo('accepted', auth()->user());
                        $canReject = $request->canTransitionTo('rejected', auth()->user());
                        $canComplete = $request->canTransitionTo('completed', auth()->user());
                        $note = match ($request->status) {
                            'pending' => $isAdmin ? 'Esperando la respuesta del proveedor.' : 'Este cliente espera tu respuesta.',
                            'accepted' => $isAdmin ? 'El proveedor la aceptó y está pendiente de terminar.' : 'Aceptada. Cuando termines el trabajo, toca «Ya terminé».',
                            'completed' => $request->review ? 'Trabajo terminado. El cliente le dio ' . $request->review->rating . ' de 5 estrellas.' : 'Trabajo terminado. El cliente ya puede dejar su reseña.',
                            'rejected' => 'Rechazada. El cliente ya lo sabe.',
                            'cancelled' => 'El cliente la canceló. No hay nada que hacer.',
                            default => null,
                        };
                    @endphp
                    <article wire:key="request-{{ $request->id }}" class="ui-card overflow-hidden {{ $request->status === 'pending' ? '!border-oro-400' : '' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3 p-6 pb-0">
                            <div class="min-w-0">
                                <h2 class="lp-title text-xl leading-snug">
                                    @if ($request->service)
                                        <a href="{{ route('home.service_details', ['service_slug' => $request->service->slug]) }}" class="hover:underline">{{ $request->service->name }}</a>
                                    @else
                                        Servicio eliminado
                                    @endif
                                </h2>
                                <p class="mt-1 text-[13px] text-grafito-500">
                                    Recibida el {{ $request->created_at->format('d/m/Y') }} a las {{ $request->created_at->format('H:i') }}
                                    @if ($isAdmin) · Proveedor: {{ $request->provider?->name ?? 'Administración' }}@endif
                                </p>
                            </div>
                            <x-ui.status :status="$request->status" :label="$request->statusLabel()" />
                        </div>

                        <dl class="grid gap-4 p-6 text-[15px] sm:grid-cols-3">
                            <div>
                                <dt class="text-[13px] text-grafito-500">Cliente</dt>
                                <dd class="mt-0.5 font-medium">{{ $request->name }}</dd>
                                <dd class="text-grafito-600">{{ $request->phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-[13px] text-grafito-500">Dónde</dt>
                                <dd class="mt-0.5 font-medium">{{ $request->zoneLabel() }}</dd>
                                @if ($request->address)<dd class="text-grafito-600">{{ $request->address }}</dd>@endif
                            </div>
                            <div>
                                <dt class="text-[13px] text-grafito-500">Para cuándo</dt>
                                <dd class="mt-0.5 font-medium">{{ $request->preferred_date?->format('d/m/Y') ?? 'A convenir' }}</dd>
                            </div>
                            @if ($request->message)
                                <div class="sm:col-span-3">
                                    <dt class="text-[13px] text-grafito-500">Lo que necesita</dt>
                                    <dd class="mt-1 rounded-2xl bg-humo-100 px-4 py-3 text-grafito-600">{{ $request->message }}</dd>
                                </div>
                            @endif
                        </dl>

                        <div class="flex flex-col gap-4 border-t border-humo-200 bg-humo-50 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                            <p class="text-[14px] text-grafito-600">{{ $note }}</p>
                            <div class="flex flex-wrap gap-2">
                                @if ($wa && in_array($request->status, ['pending', 'accepted'], true))
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm"><x-site.icon name="whatsapp" class="h-4 w-4" /> Escribirle</a>
                                @endif
                                @if ($canAccept)
                                    <button type="button" wire:click="changeStatus({{ $request->id }}, 'accepted')" wire:loading.attr="disabled" class="ui-btn ui-btn-primary ui-btn-sm"><x-site.icon name="check" class="h-4 w-4" /> Aceptar</button>
                                @endif
                                @if ($canReject)
                                    <button type="button" wire:click="changeStatus({{ $request->id }}, 'rejected')" wire:confirm="¿Seguro que quieres rechazar esta solicitud? El cliente lo verá y no podrás deshacerlo." wire:loading.attr="disabled" class="ui-btn ui-btn-danger ui-btn-sm">Rechazar</button>
                                @endif
                                @if ($canComplete)
                                    <button type="button" wire:click="changeStatus({{ $request->id }}, 'completed')" wire:confirm="¿Ya terminaste este trabajo? El cliente podrá dejarte una reseña." wire:loading.attr="disabled" class="ui-btn ui-btn-success ui-btn-sm"><x-site.icon name="check" class="h-4 w-4" /> Ya terminé</button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $requests->links() }}
        @endif
    </x-ui.panel>
</div>
