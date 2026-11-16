<div>
    <x-ui.panel title="Historial" accent="de la administración" help="Todo lo que se hizo desde este panel: quién, qué y cuándo. Sirve para saber quién cambió algo y para notar un acceso que no reconoces.">
        <div class="relative mb-6">
            <x-site.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-grafito-400" />
            <input type="search" class="ui-input pl-11" wire:model.live.debounce.400ms="q" placeholder="Buscar: suspendió, pago, sello, un correo…" aria-label="Buscar en el historial">
        </div>

        @if ($entries->isEmpty())
            <x-ui.empty icon="list" title="{{ $q !== '' ? 'Nada coincide con la búsqueda' : 'Todavía no hay acciones' }}">
                Cada cambio que hagas en el panel (aprobar un pago, suspender una cuenta, cambiar un texto) quedará anotado aquí.
            </x-ui.empty>
        @else
            <ol class="ui-card divide-y divide-humo-200">
                @foreach ($entries as $entry)
                    @php $when = \Illuminate\Support\Carbon::parse($entry->created_at); @endphp
                    <li wire:key="log-{{ $entry->id }}" class="flex flex-col gap-1 px-5 py-4 sm:flex-row sm:items-start sm:gap-6">
                        <div class="shrink-0 text-[13px] text-grafito-500 sm:w-36">{{ $when->format('d/m/Y H:i') }}<span class="block">{{ $when->diffForHumans() }}</span></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[15px]"><span class="font-medium">{{ $entry->user_name ?? 'Cuenta eliminada' }}</span> · {{ $entry->action }}@if ($entry->subject): <span class="break-words">{{ $entry->subject }}</span>@endif</p>
                            @if ($entry->details)<p class="mt-0.5 break-words text-[14px] text-grafito-500">{{ \Illuminate\Support\Str::limit($entry->details, 300) }}</p>@endif
                        </div>
                        <div class="shrink-0 text-[12px] text-grafito-400">{{ $entry->ip }}</div>
                    </li>
                @endforeach
            </ol>
            <div class="mt-6">{{ $entries->links() }}</div>
        @endif
    </x-ui.panel>
</div>
