<div>
    <x-ui.panel title="Verifica" accent="tu perfil" help="Es voluntario y gratis. Cada documento que entregues te da un sello en tu perfil: es lo que hace que un cliente te elija a ti y no a otro.">
        <div class="space-y-4">
            @foreach ($types as $type => $meta)
                @php
                    $document = $latest->get($type);
                    $badge = $provider->badgeFor($type);
                    $expired = $type === \App\Models\ProviderDocument::BACKGROUND && $provider->background_checked_at && ! $badge;
                @endphp
                <article wire:key="document-{{ $type }}" class="ui-card ui-card-pad">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 max-w-2xl">
                            <h2 class="lp-title text-xl leading-snug">{{ $meta['label'] }}</h2>
                            <p class="mt-1 text-[15px] leading-relaxed text-grafito-500">{{ $meta['why'] }}</p>
                        </div>
                        @if ($badge)
                            <x-ui.status status="approved" label="Sello activo" />
                        @elseif ($expired)
                            <x-ui.status status="pending" label="Sello vencido" />
                        @elseif ($document)
                            <x-ui.status :status="$document->status" :label="$document->statusLabel()" />
                        @endif
                    </div>

                    @if ($badge)
                        <p class="mt-4 flex items-start gap-2.5 text-[15px]">
                            <x-site.icon name="shield" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" />
                            <span><span class="font-medium">{{ $badge['label'] }}</span> <span class="block text-[13px] text-grafito-500">{{ $badge['detail'] }}. Así lo ven los clientes.</span></span>
                        </p>
                    @elseif ($expired)
                        <p class="mt-4 text-[15px] text-amber-900">Tu sello venció y ya no aparece en tu perfil. Saca un certificado nuevo y vuelve a subirlo para recuperarlo.</p>
                    @endif

                    @if ($document?->status === 'pending')
                        <p class="mt-4 text-[15px] text-grafito-600">Lo estamos revisando. Te avisaremos por correo.</p>
                    @elseif ($document?->status === 'rejected' && ! $badge)
                        <p class="mt-4 text-[15px] text-grafito-600"><span class="font-medium">No pudimos darte el sello:</span> {{ $document->rejection_reason }} Puedes seguir usando Malakplace con normalidad.</p>
                    @endif

                    @if ($uploading === $type)
                        <form wire:submit="submit" class="mt-6 space-y-5 border-t border-humo-200 pt-6">
                            <p class="text-[15px] leading-relaxed text-grafito-600">{{ $meta['how'] }}</p>
                            @if ($type === \App\Models\ProviderDocument::BACKGROUND)
                                <a href="{{ $portal }}" target="_blank" rel="noopener" class="ui-link inline-flex items-center gap-1.5 text-[15px]">Sacar el certificado en el Ministerio del Interior <x-site.icon name="arrow-up-right" class="h-4 w-4" /></a>
                            @endif
                            <x-ui.field label="Foto o PDF" name="document" for="document-file" hint="Una foto del celular sirve, si se lee bien.">
                                <x-ui.photo-input id="document-file" model="document" pdf :max-side="2000" empty="Toca para elegir el documento" />
                            </x-ui.field>
                            <div>
                                <label class="flex cursor-pointer items-start gap-3 text-[14px] leading-snug text-grafito-600">
                                    <input type="checkbox" wire:model="consent" class="mt-0.5 h-5 w-5 shrink-0">
                                    <span>{{ $meta['consent'] }}</span>
                                </label>
                                @error('consent')
                                    <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit" class="ui-btn ui-btn-primary ui-submit" wire:loading.attr="disabled" wire:target="submit,document">
                                    <span wire:loading.remove wire:target="submit">Enviar a revisión</span>
                                    <span wire:loading wire:target="submit">Enviando…</span>
                                </button>
                                <button type="button" wire:click="cancelUpload" class="ui-btn ui-btn-ghost">Cancelar</button>
                            </div>
                        </form>
                    @else
                        <div class="mt-5 flex flex-wrap gap-2">
                            @if ($document?->status !== 'pending')
                                <button type="button" wire:click="startUpload('{{ $type }}')" class="ui-btn {{ $badge ? 'ui-btn-secondary' : 'ui-btn-primary' }} ui-btn-sm">
                                    <x-site.icon name="upload" class="h-4 w-4" /> {{ $badge ? 'Renovar' : 'Subir documento' }}
                                </button>
                            @endif
                            @if ($badge || $document?->status === 'pending')
                                <button type="button" wire:click="withdraw('{{ $type }}')" wire:confirm="Se borrará el documento y el sello dejará de aparecer en tu perfil. ¿Quieres retirarlo?" class="ui-btn ui-btn-ghost ui-btn-sm">Retirar</button>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach

            <p class="text-[14px] leading-relaxed text-grafito-500">Tus documentos solo los ve la administración y se borran apenas los revisamos. Nunca se publican: en tu perfil solo aparece el sello y su fecha. Sin sellos puedes publicar y trabajar igual.</p>
        </div>
    </x-ui.panel>
</div>
