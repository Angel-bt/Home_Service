<div>
    <x-ui.panel title="Hola, {{ \Illuminate\Support\Str::before(auth()->user()->name . ' ', ' ') }}" help="Empieza por lo pendiente. Si aquí no hay nada, todo está al día.">
        <div class="space-y-8">
            {{-- Lo que requiere atención --}}
            <section>
                <h2 class="lp-title mb-4 text-2xl">Qué tienes pendiente</h2>
                @if ($tasks === [])
                    <div class="ui-card flex items-center gap-4 p-6">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-800"><x-site.icon name="check" class="h-6 w-6" /></span>
                        <div>
                            <p class="text-[16px] font-medium">Todo al día</p>
                            <p class="text-[14px] text-grafito-500">Pagos, documentos, mensajes y solicitudes están al día.</p>
                        </div>
                    </div>
                @else
                    <ul class="ui-card divide-y divide-humo-200 overflow-hidden">
                        @foreach ($tasks as $i => $task)
                            <li class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center {{ $i === 0 ? 'bg-oro-300/15' : '' }}">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-humo-100 text-grafito-600"><x-site.icon :name="$task['icon']" class="h-5 w-5" /></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[16px] font-medium">{{ $task['title'] }}</p>
                                    <p class="mt-0.5 text-[14px] leading-snug text-grafito-500">{{ $task['text'] }}</p>
                                </div>
                                <a href="{{ $task['href'] }}" class="ui-btn {{ $i === 0 ? 'ui-btn-primary' : 'ui-btn-secondary' }} ui-btn-sm shrink-0 self-start sm:self-auto">{{ $task['action'] }} <x-site.icon name="arrow-right" class="h-4 w-4" /></a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Cifras --}}
            <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <x-ui.stat label="Clientes" :value="$totalCustomers" hint="Cuentas registradas" />
                <x-ui.stat label="Proveedores" :value="$totalProviders" hint="Ver proveedores" :href="route('admin.service_providers')" />
                <x-ui.stat label="Servicios" :value="$totalServices" hint="Ver servicios" :href="route('admin.all_services')" />
                <x-ui.stat label="Solicitudes activas" :value="$activeRequests" :hint="$completedRequests . ' terminadas en total'" :href="route('admin.service_requests')" />
            </section>

            <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                <section class="ui-card ui-card-pad">
                    <div class="flex items-end justify-between gap-4">
                        <h2 class="lp-title text-2xl">Oficios con más servicios</h2>
                        <a href="{{ route('admin.service_categories') }}" class="ui-link shrink-0 text-[14px]">Ver los {{ $totalCategories }}</a>
                    </div>
                    @php $maxServices = max(1, (int) $topCategories->max('services_count')); @endphp
                    @if ($topCategories->isEmpty())
                        <p class="mt-5 text-[15px] text-grafito-500">Aún no hay oficios. Crea el primero en «Categorías».</p>
                    @else
                        <ul class="mt-6 space-y-4">
                            @foreach ($topCategories as $category)
                                <li>
                                    <div class="flex justify-between gap-4 text-[14px]"><span>{{ $category->name }}</span><span class="font-medium tabular-nums">{{ $category->services_count }}</span></div>
                                    <div class="mt-1.5 h-2 rounded-full bg-humo-200" aria-hidden="true">
                                        <div class="ui-bar h-full rounded-full bg-noche-900" style="width: {{ round($category->services_count / $maxServices * 100) }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="ui-card ui-card-pad flex flex-col">
                    <h2 class="lp-title text-2xl">Visitas</h2>
                    <p class="lp-title mt-4 text-5xl tabular-nums">{{ number_format((int) $visitCount) }}</p>
                    <p class="mt-2 text-[14px] text-grafito-500">Personas que entraron al sitio. Cuenta una visita por sesión.</p>
                    <button type="button" wire:click="resetVisits" wire:confirm="¿Volver el contador de visitas a cero? No se puede deshacer." class="ui-btn ui-btn-ghost ui-btn-sm mt-auto self-start !px-0 pt-5 hover:!bg-transparent">Reiniciar contador</button>
                </section>
            </div>
        </div>
    </x-ui.panel>
</div>
