{{-- Panel del cliente: cada solicitud muestra en qué paso está y qué puede hacer ahora. --}}
<div>
    <x-ui.panel title="Mis solicitudes" help="Los servicios que pediste. Aquí ves si el proveedor ya respondió y, al terminar, puedes calificarlo.">
        <x-slot:actions>
            <a href="{{ route('search') }}" class="ui-btn ui-btn-primary"><x-site.icon name="search" class="h-4 w-4" /> Buscar servicios</a>
        </x-slot:actions>

        @if ($requests->isEmpty())
            <div class="ui-card ui-card-pad">
                <h2 class="lp-title text-2xl">Pedir un servicio es <span class="lp-accent">así de simple</span></h2>
                <ol class="mt-6 grid gap-6 sm:grid-cols-3">
                    @foreach ([
                        ['Busca lo que necesitas', 'Escribe el oficio o elígelo de la lista, y mira precios y reseñas.'],
                        ['Escríbele o envía una solicitud', 'Por WhatsApp es lo más rápido. Con la solicitud queda todo aquí.'],
                        ['Paga y califica', 'Le pagas directo al proveedor y al final cuentas cómo te fue.'],
                    ] as $i => [$stepTitle, $stepText])
                        <li class="border-t border-humo-300 pt-4">
                            <span class="lp-accent text-4xl text-grafito-400">{{ $i + 1 }}</span>
                            <p class="mt-2 text-[16px] font-medium">{{ $stepTitle }}</p>
                            <p class="mt-1 text-[14px] leading-snug text-grafito-500">{{ $stepText }}</p>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('search') }}" class="ui-btn ui-btn-primary">Buscar un servicio</a>
                    <a href="{{ route('home.service_categories') }}" class="ui-btn ui-btn-secondary">Ver todos los oficios</a>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($requests as $request)
                    @php
                        $stage = ['pending' => 1, 'accepted' => 2, 'completed' => 3][$request->status] ?? null;
                        $note = match ($request->status) {
                            'pending' => 'Enviada. Esperando la respuesta del proveedor. Si tienes prisa, escríbele por WhatsApp.',
                            'accepted' => 'El proveedor aceptó. Acuerden el día y el precio; le pagas directo a él.',
                            'completed' => $request->review ? 'Trabajo terminado. ¡Gracias por tu reseña!' : 'Trabajo terminado. Cuéntanos cómo te fue: ayudas a tus vecinos a elegir.',
                            'rejected' => 'El proveedor no puede atenderte esta vez. Prueba con otro proveedor del mismo oficio.',
                            'cancelled' => 'Cancelaste esta solicitud.',
                            default => null,
                        };
                        $providerPhone = $request->provider?->utype === 'SVP' ? ($request->provider->serviceProvider?->whatsapp ?: $request->provider->phone) : null;
                        $wa = in_array($request->status, ['pending', 'accepted'], true)
                            ? \App\Support\WhatsApp::link($providerPhone ?: config('marketplace.contact_phone'), 'Hola, te escribo por mi solicitud de "' . ($request->service?->name ?? 'servicio') . '" en Malakplace.')
                            : null;
                    @endphp
                    <article wire:key="customer-request-{{ $request->id }}" class="ui-card overflow-hidden">
                        <div class="flex flex-wrap items-start justify-between gap-3 p-6 pb-4">
                            <div class="min-w-0">
                                <h2 class="lp-title text-xl leading-snug">
                                    @if ($request->service)
                                        <a href="{{ route('home.service_details', ['service_slug' => $request->service->slug]) }}" class="hover:underline">{{ $request->service->name }}</a>
                                    @else
                                        Servicio eliminado
                                    @endif
                                </h2>
                                <p class="mt-1 text-[13px] text-grafito-500">{{ $request->provider?->name ?? 'Malakplace' }} · {{ $request->zoneLabel() }} · Enviada el {{ $request->created_at->format('d/m/Y') }}</p>
                            </div>
                            <x-ui.status :status="$request->status" :label="$request->statusLabel()" />
                        </div>

                        @if ($stage)
                            {{-- Avance de la solicitud --}}
                            <ol class="grid grid-cols-3 gap-2 px-6" aria-label="Avance de la solicitud">
                                @foreach (['Enviada', 'Aceptada', 'Terminada'] as $i => $stepLabel)
                                    <li @if ($i + 1 === $stage) aria-current="step" @endif>
                                        <div class="h-1.5 rounded-full {{ $i < $stage ? 'bg-emerald-600' : 'bg-humo-200' }}"></div>
                                        <p class="mt-1.5 text-[12px] {{ $i < $stage ? 'font-medium text-grafito' : 'text-grafito-400' }}">{{ $stepLabel }}</p>
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        <div class="mt-4 flex flex-col gap-4 border-t border-humo-200 bg-humo-50 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                            <p class="text-[14px] text-grafito-600">{{ $note }}</p>
                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                @if ($wa)
                                    <a href="{{ $wa }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm"><x-site.icon name="whatsapp" class="h-4 w-4" /> Escribirle</a>
                                @endif
                                @if (in_array($request->status, ['pending', 'accepted'], true))
                                    <button type="button" wire:click="cancelRequest({{ $request->id }})" wire:confirm="¿Seguro que quieres cancelar esta solicitud? No se puede deshacer." class="ui-btn ui-btn-ghost ui-btn-sm">Cancelar solicitud</button>
                                @elseif ($request->status === 'completed' && ! $request->review && $reviewingId !== $request->id)
                                    <button type="button" wire:click="startReview({{ $request->id }})" class="ui-btn ui-btn-gold ui-btn-sm"><x-site.icon name="star" class="h-4 w-4" /> Calificar</button>
                                @elseif ($request->review)
                                    <span class="flex text-oro-500" aria-label="Le diste {{ $request->review->rating }} de 5 estrellas">
                                        @for ($i = 1; $i <= 5; $i++)<x-site.icon name="star" class="h-4 w-4 {{ $i <= $request->review->rating ? '' : 'opacity-25' }}" />@endfor
                                    </span>
                                @elseif ($request->status === 'rejected' && $request->service)
                                    <a href="{{ route('search', ['q' => $request->service->name]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">Buscar otro proveedor</a>
                                @endif
                                @if (in_array($request->status, ['completed', 'cancelled'], true) && $request->service?->isPublic())
                                    <a href="{{ route('home.service_details', ['service_slug' => $request->service->slug]) }}#solicitar" class="ui-btn ui-btn-secondary ui-btn-sm">Volver a pedir</a>
                                @endif
                            </div>
                        </div>

                        @if ($reviewingId === $request->id)
                            <form wire:submit="saveReview" class="space-y-5 border-t border-humo-200 p-6">
                                <fieldset>
                                    <legend class="ui-label">¿Cuántas estrellas le das?</legend>
                                    <div class="flex gap-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <button type="button" wire:click="$set('rating', {{ $i }})" class="{{ $i <= $rating ? 'text-oro-500' : 'text-humo-300' }} transition hover:text-oro-400" aria-label="{{ $i }} {{ $i === 1 ? 'estrella' : 'estrellas' }}" @if ($i === $rating) aria-pressed="true" @endif>
                                                <x-site.icon name="star" class="h-9 w-9" />
                                            </button>
                                        @endfor
                                    </div>
                                    @error('rating')
                                        <p class="ui-error"><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
                                    @enderror
                                </fieldset>
                                <x-ui.field label="Cuéntanos cómo fue el trabajo" name="comment" for="comment-{{ $request->id }}" optional>
                                    <textarea id="comment-{{ $request->id }}" rows="3" class="ui-input @error('comment') ui-input-invalid @enderror" wire:model="comment" placeholder="Ej.: llegó puntual y dejó todo limpio."></textarea>
                                </x-ui.field>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="ui-btn ui-btn-primary" wire:loading.attr="disabled" wire:target="saveReview">Enviar reseña</button>
                                    <button type="button" wire:click="$set('reviewingId', null)" class="ui-btn ui-btn-ghost">Ahora no</button>
                                </div>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
            {{ $requests->links() }}
        @endif
    </x-ui.panel>
</div>
