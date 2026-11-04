<div>
    <x-ui.panel title="Hola, {{ \Illuminate\Support\Str::before(auth()->user()->name . ' ', ' ') }}" help="Este es tu panel. Aquí ves si tienes clientes esperando y qué te falta por hacer.">
        <x-slot:actions>
            <a href="{{ route('sprovider.add_service') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Publicar servicio</a>
        </x-slot:actions>

        <div class="space-y-8">
            {{-- Lo más urgente primero --}}
            @if ($pendingCount > 0)
                <a href="{{ route('sprovider.requests', ['status' => 'pending']) }}" class="group flex flex-col gap-4 rounded-3xl bg-noche-900 p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-8">
                    <div>
                        <p class="lp-title text-2xl sm:text-3xl">{{ $pendingCount === 1 ? 'Un cliente espera' : $pendingCount . ' clientes esperan' }} <span class="lp-accent text-oro-300">tu respuesta</span></p>
                        <p class="mt-2 text-[15px] text-white/60">Quien contesta primero suele quedarse con el trabajo.</p>
                    </div>
                    <span class="ui-btn ui-btn-gold shrink-0 self-start sm:self-auto">Responder ahora <x-site.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" /></span>
                </a>
            @endif

            <x-ui.checklist :items="$steps" title="Para empezar a recibir clientes" />

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-ui.stat label="Servicios publicados" :value="$servicesCount . ($limit !== null ? ' de ' . $limit : '')" :hint="$limit !== null ? 'Plan gratuito' : 'Plan Pro: sin límite'" :href="route('sprovider.services')" />
                <x-ui.stat label="Por responder" :value="$pendingCount" hint="Ver solicitudes" :href="route('sprovider.requests')" />
                <x-ui.stat label="Trabajos en curso" :value="$acceptedCount" hint="Aceptados, sin terminar" />
                <x-ui.stat label="Calificación" :value="$rating ? number_format((float) $rating, 1) . ' ★' : '—'" :hint="$completedCount . ' ' . ($completedCount === 1 ? 'trabajo terminado' : 'trabajos terminados')" />
            </div>

            <section>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <h2 class="lp-title text-2xl">Últimas solicitudes</h2>
                    @if ($latestRequests->isNotEmpty())
                        <a href="{{ route('sprovider.requests') }}" class="ui-link text-[14px]">Ver todas</a>
                    @endif
                </div>
                @if ($latestRequests->isEmpty())
                    <x-ui.empty icon="inbox" title="Aún no te llegan solicitudes">
                        Cuando un cliente pida uno de tus servicios lo verás aquí, y también te puede escribir directo a tu WhatsApp.
                    </x-ui.empty>
                @else
                    <ul class="ui-card divide-y divide-humo-200">
                        @foreach ($latestRequests as $request)
                            <li>
                                <a href="{{ route('sprovider.requests') }}" class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-humo-50">
                                    <span class="min-w-0">
                                        <span class="block text-[15px] font-medium">{{ $request->name }} · {{ $request->service?->name ?? 'Servicio eliminado' }}</span>
                                        <span class="block text-[13px] text-grafito-500">{{ $request->created_at->format('d/m/Y') }} · {{ $request->zoneLabel() }}</span>
                                    </span>
                                    <x-ui.status :status="$request->status" :label="$request->statusLabel()" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            @unless ($isPro)
                <a href="{{ route('sprovider.plan') }}" class="group flex flex-col gap-3 rounded-3xl border border-oro-400 bg-oro-300/20 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[16px] font-medium">¿Ofreces más de {{ $limit }} servicios?</p>
                        <p class="mt-1 text-[14px] text-grafito-600">Con el plan Pro publicas sin límite.</p>
                    </div>
                    <span class="inline-flex shrink-0 items-center gap-2 text-[14px] font-medium">Ver plan Pro <x-site.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-0.5" /></span>
                </a>
            @endunless
        </div>
    </x-ui.panel>
</div>
