<div>
    <x-ui.panel title="Preguntas" accent="frecuentes" help="Lo que aparece en la página de ayuda. Escribe como hablarías con un vecino: respuestas cortas y con el siguiente paso.">
        <x-slot:actions>
            <a href="{{ route('home.faq') }}" target="_blank" class="ui-btn ui-btn-secondary"><x-site.icon name="eye" class="h-4 w-4" /> Ver la página</a>
        </x-slot:actions>


        <div class="space-y-8">
            @foreach ($groups as $groupName)
                <section class="ui-card overflow-hidden" wire:key="group-{{ $loop->index }}">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-humo-200 px-6 py-4">
                        <h2 class="lp-title text-2xl">{{ $groupName }}</h2>
                        <button type="button" wire:click="create(@js($groupName))" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="plus" class="h-4 w-4" /> Agregar pregunta</button>
                    </div>

                    @if ($editingId === 0 && $group === $groupName)
                        <div class="border-b border-humo-200 bg-humo-50 p-6">@include('livewire.admin.partials.faq-form', ['key' => 'new'])</div>
                    @endif

                    @php $items = $faqs->get($groupName, collect()); @endphp
                    @if ($items->isEmpty())
                        <p class="px-6 py-5 text-[15px] text-grafito-500">Todavía no hay preguntas en esta sección.</p>
                    @else
                        <ol class="divide-y divide-humo-200">
                            @foreach ($items as $faq)
                                <li wire:key="faq-{{ $faq->id }}" class="px-6 py-4">
                                    @if ($editingId === $faq->id)
                                        @include('livewire.admin.partials.faq-form', ['key' => 'faq-' . $faq->id])
                                    @else
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <p class="text-[16px] font-medium">{{ $faq->question }}</p>
                                                <p class="mt-1 line-clamp-2 text-[14px] text-grafito-500">{{ $faq->renderedAnswer() }}</p>
                                            </div>
                                            <div class="flex shrink-0 flex-wrap gap-1">
                                                <button type="button" wire:click="move({{ $faq->id }}, -1)" class="ui-btn ui-btn-ghost ui-btn-sm" @disabled($loop->first) aria-label="Subir"><x-site.icon name="chevron-down" class="h-4 w-4 rotate-180" /></button>
                                                <button type="button" wire:click="move({{ $faq->id }}, 1)" class="ui-btn ui-btn-ghost ui-btn-sm" @disabled($loop->last) aria-label="Bajar"><x-site.icon name="chevron-down" class="h-4 w-4" /></button>
                                                <button type="button" wire:click="edit({{ $faq->id }})" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="edit" class="h-4 w-4" /> Editar</button>
                                                <button type="button" wire:click="delete({{ $faq->id }})" wire:confirm="¿Quitar la pregunta «{{ $faq->question }}»?" class="ui-btn ui-btn-ghost ui-btn-sm" aria-label="Eliminar"><x-site.icon name="trash" class="h-4 w-4" /></button>
                                            </div>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            @endforeach
        </div>
    </x-ui.panel>
</div>
