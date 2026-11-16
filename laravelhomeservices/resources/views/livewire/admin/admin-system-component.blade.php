<div>
    <x-ui.panel title="Estado" accent="del sistema" help="Si algo deja de funcionar, empieza por aquí. Todo en verde: no hay nada que hacer.">
        <div class="space-y-6">
            <section class="ui-card ui-card-pad">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="lp-title text-2xl">Comprobaciones</h2>
                        <p class="mt-1 text-[14px] text-grafito-500">
                            @if ($cron)
                                La revisión automática corrió {{ $cron['when']->diffForHumans() }} ({{ $cron['when']->format('d/m/Y H:i') }}):
                                <span class="font-medium {{ $cron['ok'] ? 'text-emerald-800' : 'text-red-800' }}">{{ $cron['ok'] ? 'todo bien' : 'con problemas' }}</span>.
                            @else
                                La revisión automática corre todos los días a las 6:00. Todavía no hay registro.
                            @endif
                        </p>
                    </div>
                    <button type="button" wire:click="runChecks" class="ui-btn ui-btn-primary" wire:loading.attr="disabled" wire:target="runChecks">
                        <span wire:loading.remove wire:target="runChecks">Revisar ahora</span>
                        <span wire:loading wire:target="runChecks">Revisando… (unos segundos)</span>
                    </button>
                </div>

                <ul class="mt-6 divide-y divide-humo-200 border-t border-humo-200">
                    <li class="flex items-start gap-3 py-4">
                        <x-site.icon :name="$databaseOk ? 'check-circle' : 'alert'" class="mt-0.5 h-5 w-5 shrink-0 {{ $databaseOk ? 'text-emerald-700' : 'text-red-700' }}" />
                        <div><p class="font-medium">Base de datos</p><p class="text-[14px] text-grafito-500">{{ $databaseOk ? 'Responde (' . $database . ').' : 'No responde. El sitio no puede funcionar: revisa Supabase.' }}</p></div>
                    </li>
                    @foreach ($checks as $command => [$label, $help, $fix])
                        @php $result = $results[$command] ?? null; @endphp
                        <li class="flex items-start gap-3 py-4" wire:key="check-{{ $command }}">
                            @if ($result === null)
                                <x-site.icon name="clock" class="mt-0.5 h-5 w-5 shrink-0 text-grafito-400" />
                            @else
                                <x-site.icon :name="$result['ok'] ? 'check-circle' : 'alert'" class="mt-0.5 h-5 w-5 shrink-0 {{ $result['ok'] ? 'text-emerald-700' : 'text-red-700' }}" />
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium">{{ $label }}</p>
                                <p class="text-[14px] text-grafito-500">{{ $help }}</p>
                                @if ($result)
                                    <p class="mt-1 whitespace-pre-line break-words text-[13px] {{ $result['ok'] ? 'text-emerald-800' : 'text-red-800' }}">{{ $result['detail'] ?: ($result['ok'] ? 'Bien.' : 'Falló.') }}</p>
                                    @unless ($result['ok'])
                                        <p class="mt-2 rounded-xl bg-amber-50 px-3 py-2 text-[14px] text-amber-950">{{ $fix }}</p>
                                    @endunless
                                @endif
                            </div>
                        </li>
                    @endforeach
                    <li class="flex items-start gap-3 py-4">
                        <x-site.icon :name="$openErrors === 0 ? 'check-circle' : 'alert'" class="mt-0.5 h-5 w-5 shrink-0 {{ $openErrors === 0 ? 'text-emerald-700' : 'text-amber-700' }}" />
                        <div>
                            <p class="font-medium">Errores del sitio</p>
                            <p class="text-[14px] text-grafito-500">{{ $openErrors === 0 ? 'Ninguno sin revisar.' : $openErrors . ' sin revisar.' }} <a href="{{ route('admin.errors') }}" class="ui-link">Ver errores</a></p>
                        </div>
                    </li>
                </ul>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="ui-card ui-card-pad">
                    <h2 class="lp-title text-2xl">Descargar datos</h2>
                    <p class="mt-1 text-[14px] text-grafito-500">Una copia para Excel. Tiene datos personales: guárdala en un lugar seguro y no la compartas.</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($exports as $type => $label)
                            <a href="{{ route('admin.export', ['type' => $type]) }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="upload" class="h-4 w-4 rotate-180" /> {{ $label }}</a>
                        @endforeach
                        <a href="{{ route('admin.activity_export') }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="upload" class="h-4 w-4 rotate-180" /> Actividad</a>
                    </div>
                    @if ($counts)
                        <dl class="mt-6 grid grid-cols-2 gap-4 text-[15px] sm:grid-cols-4">
                            @foreach ($counts as $label => $count)
                                <div><dt class="text-[13px] text-grafito-500">{{ $label }}</dt><dd class="text-[22px] font-medium">{{ number_format($count) }}</dd></div>
                            @endforeach
                        </dl>
                    @endif
                </section>

                <section class="ui-card ui-card-pad">
                    <h2 class="lp-title text-2xl">Versión</h2>
                    <p class="mt-1 text-[14px] text-grafito-500">Útil si alguien que mantiene el sitio te lo pregunta.</p>
                    <dl class="mt-5 divide-y divide-humo-200 text-[15px]">
                        @foreach ($version as $label => $value)
                            <div class="flex flex-col gap-0.5 py-2.5 sm:flex-row sm:gap-6"><dt class="text-grafito-500 sm:w-40 sm:shrink-0">{{ $label }}</dt><dd class="min-w-0 break-words">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                </section>
            </div>
        </div>
    </x-ui.panel>
</div>
