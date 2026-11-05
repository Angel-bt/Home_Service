<div>
    <x-ui.panel title="Actividad" accent="del sitio" help="Qué busca la gente, qué no encuentra y qué servicios les interesan. No se cuenta tu propia actividad ni la de los robots de los buscadores.">
        <x-slot:actions>
            <a href="{{ route('admin.activity_export', ['days' => $days]) }}" class="ui-btn ui-btn-secondary"><x-site.icon name="upload" class="h-4 w-4" /> Descargar para Excel</a>
        </x-slot:actions>

        <div class="space-y-8">
            <div class="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Elegir periodo">
                @foreach ($periods as $value => $label)
                    <button type="button" wire:click="$set('days', {{ $value }})" @if ($days === $value) aria-pressed="true" @endif
                            class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $days === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @unless ($hasData)
                <x-ui.empty icon="chart" title="Todavía no hay actividad en este periodo">
                    Se empieza a contar desde que se activó este panel. Cuando la gente busque y mire servicios, lo verás aquí.
                </x-ui.empty>
            @else
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <x-ui.stat label="Búsquedas" :value="number_format($counts[\App\Models\ActivityEvent::SEARCH] ?? 0)" />
                    <x-ui.stat label="Visitas a servicios" :value="number_format($counts[\App\Models\ActivityEvent::SERVICE_VIEW] ?? 0)" />
                    <x-ui.stat label="Toques en WhatsApp" :value="number_format($counts[\App\Models\ActivityEvent::WHATSAPP_CLICK] ?? 0)" hint="Gente que quiso contactar" />
                    <x-ui.stat label="Solicitudes" :value="number_format($counts[\App\Models\ActivityEvent::REQUEST_CREATED] ?? 0)" :hint="($counts[\App\Models\ActivityEvent::REQUEST_COMPLETED] ?? 0) . ' terminadas'" />
                    <x-ui.stat label="Clientes nuevos" :value="number_format($signups['CST'] ?? 0)" />
                    <x-ui.stat label="Proveedores nuevos" :value="number_format($signups['SVP'] ?? 0)" />
                    <x-ui.stat label="Cuentas activas" :value="number_format($activeUsers)" hint="Hicieron algo en el periodo" />
                    <x-ui.stat label="Reseñas" :value="number_format($counts[\App\Models\ActivityEvent::REVIEW_CREATED] ?? 0)" />
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <section class="ui-card ui-card-pad">
                        <h2 class="lp-title text-2xl">Lo más buscado</h2>
                        <p class="mt-1 text-[14px] text-grafito-500">Lo que la gente quiere: que no falte en la página.</p>
                        @if ($topSearches->isEmpty())
                            <p class="mt-5 text-[15px] text-grafito-500">Aún no hay búsquedas.</p>
                        @else
                            <ol class="mt-5 divide-y divide-humo-200 text-[15px]">
                                @foreach ($topSearches as $row)
                                    <li class="flex items-center justify-between gap-4 py-2.5"><span class="min-w-0 break-words">{{ $row->term }}</span><span class="shrink-0 font-medium">{{ $row->total }}</span></li>
                                @endforeach
                            </ol>
                        @endif
                    </section>

                    <section class="ui-card ui-card-pad">
                        <h2 class="lp-title text-2xl">Buscado <span class="lp-accent">sin resultados</span></h2>
                        <p class="mt-1 text-[14px] text-grafito-500">Oficios que te faltan: consigue proveedores de esto primero.</p>
                        @if ($missingSearches->isEmpty())
                            <p class="mt-5 text-[15px] text-grafito-500">Nada por ahora: todo lo que buscaron tuvo resultados.</p>
                        @else
                            <ol class="mt-5 divide-y divide-humo-200 text-[15px]">
                                @foreach ($missingSearches as $row)
                                    <li class="flex items-center justify-between gap-4 py-2.5"><span class="min-w-0 break-words">{{ $row->term }}</span><span class="shrink-0 font-medium">{{ $row->total }}</span></li>
                                @endforeach
                            </ol>
                        @endif
                    </section>

                    <section class="ui-card ui-card-pad">
                        <h2 class="lp-title text-2xl">Servicios más vistos</h2>
                        <p class="mt-1 text-[14px] text-grafito-500">Muchas visitas y pocos toques en WhatsApp suele querer decir que falta una buena foto o un mejor precio.</p>
                        @if ($topServices->isEmpty())
                            <p class="mt-5 text-[15px] text-grafito-500">Aún no hay visitas.</p>
                        @else
                            <table class="mt-5 w-full text-[15px]">
                                <thead><tr class="text-left text-[13px] text-grafito-500"><th class="pb-2 font-normal">Servicio</th><th class="pb-2 pl-3 text-right font-normal">Visitas</th><th class="pb-2 pl-3 text-right font-normal">WhatsApp</th></tr></thead>
                                <tbody class="divide-y divide-humo-200">
                                    @foreach ($topServices as $row)
                                        <tr>
                                            <td class="py-2.5 break-words">
                                                @if ($row['slug'])<a href="{{ route('home.service_details', ['service_slug' => $row['slug']]) }}" class="hover:underline">{{ $row['name'] }}</a>@else{{ $row['name'] }}@endif
                                            </td>
                                            <td class="py-2.5 pl-3 text-right font-medium">{{ $row['views'] }}</td>
                                            <td class="py-2.5 pl-3 text-right font-medium">{{ $row['contacts'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </section>

                    <section class="ui-card ui-card-pad">
                        <h2 class="lp-title text-2xl">Actividad por zona</h2>
                        <p class="mt-1 text-[14px] text-grafito-500">Solo cuenta a quienes eligieron una zona.</p>
                        @if ($zones->isEmpty())
                            <p class="mt-5 text-[15px] text-grafito-500">Aún nadie eligió una zona.</p>
                        @else
                            <ol class="mt-5 divide-y divide-humo-200 text-[15px]">
                                @foreach ($zones as $zone => $total)
                                    <li class="flex items-center justify-between gap-4 py-2.5"><span>{{ $zoneLabels[$zone] ?? $zone }}</span><span class="font-medium">{{ $total }}</span></li>
                                @endforeach
                            </ol>
                        @endif
                    </section>
                </div>
            @endunless
        </div>
    </x-ui.panel>
</div>
