<div>
    <x-ui.panel title="Reseñas" help="Lo que opinan los clientes. Oculta una reseña solo si es ofensiva, falsa o tiene datos de otras personas: una mala calificación honesta debe quedarse.">
        <div class="-mx-5 mb-6 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar reseñas">
            @foreach (['' => ['Todas', null], 'low' => ['1 o 2 estrellas', $lowCount], 'hidden' => ['Ocultas', $hiddenCount]] as $value => [$label, $count])
                <button type="button" wire:click="$set('filter', '{{ $value }}')" @if ($filter === $value) aria-pressed="true" @endif
                        class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $filter === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                    {{ $label }}@if ($count !== null) <span class="{{ $filter === $value ? 'text-white/60' : 'text-grafito-400' }}">{{ $count }}</span>@endif
                </button>
            @endforeach
        </div>

        @if ($reviews->isEmpty())
            <x-ui.empty icon="star" title="No hay reseñas con este filtro">
                Las reseñas aparecen cuando un cliente califica un trabajo terminado.
            </x-ui.empty>
        @else
            <div class="space-y-3">
                @foreach ($reviews as $review)
                    <article wire:key="review-{{ $review->id }}" class="ui-card overflow-hidden {{ $review->hidden_at ? 'opacity-70' : '' }}">
                        <div class="p-5">
                            <div class="flex flex-wrap items-center gap-3 text-[14px]">
                                <span class="flex text-oro-500" aria-label="{{ $review->rating }} de 5 estrellas">
                                    @for ($i = 1; $i <= 5; $i++)<x-site.icon name="star" class="h-4 w-4 {{ $i <= $review->rating ? '' : 'opacity-25' }}" />@endfor
                                </span>
                                <span class="font-medium">{{ $review->user?->name ?? 'Cliente eliminado' }}</span>
                                <span class="text-grafito-400">{{ $review->created_at?->format('d/m/Y') }}</span>
                                @if ($review->hidden_at)<x-ui.status status="cancelled" label="Oculta" />@endif
                            </div>
                            <p class="mt-1 text-[14px] text-grafito-500">
                                Sobre
                                @if ($review->service)
                                    <a href="{{ route('home.service_details', ['service_slug' => $review->service->slug]) }}" class="ui-link">{{ $review->service->name }}</a>
                                    · {{ $review->service->user?->name ?? 'Administración' }}
                                @else
                                    un servicio eliminado
                                @endif
                            </p>
                            @if ($review->comment)
                                <blockquote class="mt-3 whitespace-pre-line rounded-2xl bg-humo-100 px-4 py-3 text-[15px] leading-relaxed text-grafito-600">{{ $review->comment }}</blockquote>
                            @endif
                            @if ($review->hidden_at && $review->hidden_reason)
                                <p class="mt-3 text-[14px] text-grafito-500">Motivo para ocultarla: {{ $review->hidden_reason }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-2 border-t border-humo-200 bg-humo-50 px-5 py-3">
                            @if ($review->hidden_at)
                                <button type="button" wire:click="show({{ $review->id }})" class="ui-btn ui-btn-secondary ui-btn-sm">Mostrar otra vez</button>
                            @elseif ($hidingId !== $review->id)
                                <button type="button" wire:click="startHide({{ $review->id }})" class="ui-btn ui-btn-ghost ui-btn-sm">Ocultar</button>
                            @endif
                        </div>
                        @if ($hidingId === $review->id)
                            <form wire:submit="hide" class="space-y-4 border-t border-humo-200 p-5">
                                <x-ui.field label="¿Por qué la ocultas?" name="hideReason" for="hide-{{ $review->id }}" hint="Queda como nota para la administración.">
                                    <input type="text" id="hide-{{ $review->id }}" class="ui-input @error('hideReason') ui-input-invalid @enderror" wire:model="hideReason" placeholder="Ej.: insultos, o menciona el teléfono de otra persona">
                                </x-ui.field>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">Ocultar reseña</button>
                                    <button type="button" wire:click="$set('hidingId', null)" class="ui-btn ui-btn-ghost ui-btn-sm">Cancelar</button>
                                </div>
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
            {{ $reviews->links() }}
        @endif
    </x-ui.panel>
</div>
