<div>
    <x-ui.panel title="Errores" accent="del sitio" help="Cosas que fallaron en el sitio, agrupadas. La mayoría se arreglan solas (un corte de internet, el correo un momento). Si uno se repite mucho, copia el detalle y envíaselo a quien mantiene el sitio.">
        <x-slot:actions>
            <a href="{{ route('admin.system') }}" class="ui-btn ui-btn-secondary">Estado del sistema</a>
            @if (! $showResolved && $logs->total() > 0)
                <button type="button" wire:click="resolveAll" wire:confirm="¿Marcar todos como revisados? Si alguno vuelve a pasar, aparecerá otra vez." class="ui-btn ui-btn-primary">Marcar todos como revisados</button>
            @endif
        </x-slot:actions>

        <label class="mb-6 flex cursor-pointer items-center gap-3 text-[15px]">
            <input type="checkbox" wire:model.live="showResolved" class="h-5 w-5"> Mostrar también los ya revisados
        </label>

        @if ($logs->isEmpty())
            <x-ui.empty icon="check-circle" title="Sin errores por revisar">
                Todo funciona. Si algo falla, quedará anotado aquí y en «Estado del sistema».
            </x-ui.empty>
        @else
            <div class="space-y-3">
                @foreach ($logs as $error)
                    @php $detail = $error->exception . "\n" . $error->message . "\n" . $error->location . "\n" . $error->url . "\nVeces: " . $error->count . ' · Última: ' . $error->last_seen_at; @endphp
                    <article wire:key="error-{{ $error->id }}" class="ui-card overflow-hidden {{ $error->resolved_at ? 'opacity-70' : '' }}">
                        <div class="p-5">
                            <div class="flex flex-wrap items-center gap-2 text-[13px] text-grafito-500">
                                <span class="rounded-full bg-humo-200 px-2.5 py-0.5 font-medium text-grafito-600">{{ $error->count }} {{ $error->count == 1 ? 'vez' : 'veces' }}</span>
                                <span>Última: {{ \Illuminate\Support\Carbon::parse($error->last_seen_at)->diffForHumans() }}</span>
                                <span>· {{ $error->url }}</span>
                                @if ($error->resolved_at)<x-ui.status status="completed" label="Revisado" />@endif
                            </div>
                            <p class="mt-2 break-words text-[15px] font-medium">{{ \Illuminate\Support\Str::limit($error->message, 300) }}</p>
                            <p class="mt-1 break-words font-mono text-[12px] text-grafito-500">{{ class_basename($error->exception) }} · {{ $error->location }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 border-t border-humo-200 bg-humo-50 px-5 py-3" x-data="{ copied: false }">
                            <button type="button" class="ui-btn ui-btn-ghost ui-btn-sm" @click="navigator.clipboard?.writeText(@js($detail)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })">
                                <span x-show="!copied">Copiar el detalle</span><span x-show="copied" x-cloak>Copiado</span>
                            </button>
                            @unless ($error->resolved_at)
                                <button type="button" wire:click="resolve({{ $error->id }})" class="ui-btn ui-btn-secondary ui-btn-sm">Marcar como revisado</button>
                            @endunless
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $logs->links() }}
        @endif
    </x-ui.panel>
</div>
