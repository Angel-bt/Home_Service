<div>
    <x-ui.panel title="Verificación" accent="de proveedores" help="Abre el documento, compáralo con lo que dice abajo y decide. Al aprobar o rechazar, el archivo se borra para siempre: solo queda el resultado y la fecha.">
        <div class="-mx-5 mb-6 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar por estado">
            @foreach (['pending' => 'En revisión', 'approved' => 'Aprobados', 'rejected' => 'Sin sello', '' => 'Todos'] as $value => $label)
                <button type="button" wire:click="$set('status', '{{ $value }}')" @if ($status === $value) aria-pressed="true" @endif
                        class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $status === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                    {{ $label }}@if ($value === 'pending') <span class="{{ $status === $value ? 'text-white/60' : 'text-grafito-400' }}">{{ $pendingCount }}</span>@endif
                </button>
            @endforeach
        </div>

        @if ($documents->isEmpty())
            <x-ui.empty icon="shield" :title="$status === 'pending' ? 'No hay documentos por revisar' : 'No hay documentos con este estado'">
                {{ $status === 'pending' ? 'Cuando un proveedor suba un documento te llegará un correo y aparecerá aquí.' : 'Prueba con otro filtro.' }}
            </x-ui.empty>
        @else
            <div class="space-y-4">
                @foreach ($documents as $document)
                    <article wire:key="document-{{ $document->id }}" class="ui-card overflow-hidden {{ $document->status === 'pending' ? '!border-oro-400' : '' }}">
                        <div class="flex flex-wrap items-start justify-between gap-3 p-6">
                            <div class="min-w-0">
                                <h2 class="lp-title text-xl leading-snug">{{ $document->label() }}</h2>
                                <p class="mt-1 break-words text-[14px] text-grafito-500">
                                    {{ $document->provider?->user?->name ?? 'Proveedor eliminado' }}
                                    @if ($document->provider?->user?->email) · {{ $document->provider->user->email }}@endif
                                    @if ($document->provider?->user?->phone) · {{ $document->provider->user->phone }}@endif
                                </p>
                                <p class="mt-1 text-[13px] text-grafito-400">Enviado el {{ $document->created_at->format('d/m/Y H:i') }}, con permiso del proveedor.</p>
                            </div>
                            <x-ui.status :status="$document->status" :label="$document->statusLabel()" />
                        </div>

                        @if ($document->status === 'pending')
                            <p class="mx-6 mb-6 rounded-2xl bg-humo-100 px-4 py-3 text-[14px] leading-relaxed text-grafito-600"><span class="font-medium text-grafito">Qué revisar:</span> {{ $types[$document->type]['review'] ?? '' }}</p>
                        @elseif ($document->status === 'rejected' && $document->rejection_reason)
                            <p class="mx-6 mb-6 text-[14px] text-grafito-600"><span class="font-medium">Motivo:</span> {{ $document->rejection_reason }}</p>
                        @endif

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-humo-200 bg-humo-50 px-6 py-4">
                            <div class="text-[14px] text-grafito-600">
                                @if ($document->path)
                                    <a href="{{ route('admin.provider_document', $document) }}" target="_blank" rel="noopener" class="ui-link inline-flex items-center gap-1.5"><x-site.icon name="eye" class="h-4 w-4" /> Ver documento</a>
                                @else
                                    <span class="text-grafito-500">El archivo ya se borró.</span>
                                @endif
                                @if ($document->reviewer)<span class="block text-[13px] text-grafito-500">Revisado por {{ $document->reviewer->name }} el {{ $document->reviewed_at?->format('d/m/Y') }}</span>@endif
                            </div>
                            @if ($document->status === 'pending' && $rejectingId !== $document->id)
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="approve({{ $document->id }})" wire:confirm="¿Revisaste el documento? Se publicará el sello «{{ $types[$document->type]['badge'] ?? '' }}» y el archivo se borrará." class="ui-btn ui-btn-success ui-btn-sm"><x-site.icon name="check" class="h-4 w-4" /> Dar el sello</button>
                                    <button type="button" wire:click="startReject({{ $document->id }})" class="ui-btn ui-btn-danger ui-btn-sm">No dar el sello</button>
                                </div>
                            @elseif ($document->status === 'approved' && $document->provider?->badgeFor($document->type))
                                <button type="button" wire:click="revoke({{ $document->id }})" wire:confirm="El sello dejará de aparecer en el perfil de este proveedor. ¿Quieres quitarlo?" class="ui-btn ui-btn-ghost ui-btn-sm">Quitar sello</button>
                            @endif
                        </div>

                        @if ($rejectingId === $document->id)
                            <form wire:submit="reject" wire:confirm="El archivo se borrará y no podrás volver a verlo. ¿Continuar?" class="space-y-4 border-t border-humo-200 p-6">
                                <x-ui.field label="¿Por qué no das el sello?" name="rejectReason" for="reject-{{ $document->id }}" hint="El proveedor verá este motivo. Dile qué puede hacer y no menciones delitos ni detalles del certificado.">
                                    <input type="text" id="reject-{{ $document->id }}" class="ui-input @error('rejectReason') ui-input-invalid @enderror" wire:model="rejectReason" placeholder="Ej.: la foto no se lee bien, envía otra">
                                </x-ui.field>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">No dar el sello</button>
                                    <button type="button" wire:click="$set('rejectingId', null)" class="ui-btn ui-btn-ghost ui-btn-sm">Volver</button>
                                </div>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
            {{ $documents->links() }}
        @endif
    </x-ui.panel>
</div>
