<div>
    <x-ui.panel title="Proveedores" help="Las personas que ofrecen servicios. Si a alguno le falta WhatsApp o zonas, los clientes no lo encuentran: vale la pena avisarle.">
        @if ($sproviders->isEmpty())
            <x-ui.empty icon="users" title="Todavía no hay proveedores">Cuando alguien cree una cuenta como proveedor, aparecerá aquí.</x-ui.empty>
        @else
            <ul class="space-y-3">
                @foreach ($sproviders as $sprovider)
                    @php
                        $complete = $sprovider->whatsapp && $sprovider->zones->isNotEmpty();
                        $phone = $sprovider->whatsapp ?: $sprovider->user?->phone;
                        $wa = \App\Support\WhatsApp::link((string) $phone, 'Hola ' . $sprovider->user?->name . ', te escribimos de Malakplace.');
                    @endphp
                    <li wire:key="provider-{{ $sprovider->id }}" class="ui-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <img src="{{ asset('images/sproviders/' . ($sprovider->image ?: 'default.jpg')) }}" alt="" loading="lazy" class="h-14 w-14 shrink-0 rounded-full object-cover">
                        <div class="min-w-0 flex-1">
                            <h2 class="flex flex-wrap items-center gap-2 text-[17px] font-medium">
                                {{ $sprovider->user?->name ?? 'Usuario eliminado' }}
                                @if ($sprovider->user?->isPro())<x-ui.status status="approved" label="Pro" />@endif
                                @unless ($complete)<x-ui.status status="pending" label="Perfil incompleto" />@endunless
                            </h2>
                            <p class="mt-0.5 break-words text-[14px] text-grafito-500">
                                {{ $sprovider->user?->email }}@if ($phone) · {{ $phone }}@endif
                            </p>
                            <p class="mt-0.5 text-[14px] text-grafito-500">
                                {{ $sprovider->category?->name ?? 'Sin oficio' }} · {{ $sprovider->zones->isNotEmpty() ? $sprovider->zones->map->label()->implode(', ') : 'Sin zonas' }}
                            </p>
                        </div>
                        @if ($wa)
                            <a href="{{ $wa }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm self-start sm:self-auto"><x-site.icon name="whatsapp" class="h-4 w-4" /> Escribirle</a>
                        @endif
                    </li>
                @endforeach
            </ul>
            {{ $sproviders->links() }}
        @endif
    </x-ui.panel>
</div>
