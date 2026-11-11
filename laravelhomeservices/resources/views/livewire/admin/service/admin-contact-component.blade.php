<div>
    <x-ui.panel title="Mensajes" help="Lo que la gente escribe desde la página de contacto. Respóndeles por WhatsApp o correo y márcalo como respondido para que no quede pendiente.">
        <div class="-mx-5 mb-6 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0" role="group" aria-label="Filtrar mensajes">
            @foreach (['pending' => 'Sin responder', 'answered' => 'Respondidos', '' => 'Todos'] as $value => $label)
                <button type="button" wire:click="$set('filter', '{{ $value }}')" @if ($filter === $value) aria-pressed="true" @endif
                        class="shrink-0 rounded-full border px-4 py-2 text-[14px] transition {{ $filter === $value ? 'border-noche-900 bg-noche-900 text-white' : 'border-humo-300 bg-white text-grafito-600 hover:border-grafito-400' }}">
                    {{ $label }}@if ($value === 'pending') <span class="{{ $filter === $value ? 'text-white/60' : 'text-grafito-400' }}">{{ $pendingCount }}</span>@endif
                </button>
            @endforeach
        </div>

        @if ($contacts->isEmpty())
            <x-ui.empty icon="mail" :title="$filter === 'pending' ? 'No hay mensajes sin responder' : 'No hay mensajes con este filtro'">
                {{ $filter === 'pending' ? 'Todo al día. Cuando alguien escriba desde la página de contacto, lo verás aquí.' : 'Prueba con otro filtro.' }}
            </x-ui.empty>
        @else
            <div class="space-y-3">
                @foreach ($contacts as $contact)
                    @php $wa = \App\Support\WhatsApp::link((string) $contact->phone, 'Hola ' . $contact->name . ', te escribimos de Malakplace por tu mensaje.'); @endphp
                    <article wire:key="contact-{{ $contact->id }}" class="ui-card overflow-hidden {{ $contact->answered_at ? '' : '!border-oro-400' }}">
                        <div class="p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 class="flex flex-wrap items-center gap-2 text-[17px] font-medium">{{ $contact->name }}
                                        @if ($contact->answered_at)<x-ui.status status="completed" label="Respondido" />@endif
                                    </h2>
                                    <p class="mt-0.5 break-words text-[13px] text-grafito-500">{{ $contact->created_at?->format('d/m/Y H:i') }} · {{ $contact->phone }} · {{ $contact->email }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @if ($wa)
                                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="ui-btn ui-btn-whatsapp ui-btn-sm"><x-site.icon name="whatsapp" class="h-4 w-4" /> Responder</a>
                                    @endif
                                    <a href="mailto:{{ $contact->email }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="mail" class="h-4 w-4" /> Correo</a>
                                </div>
                            </div>
                            <p class="mt-4 whitespace-pre-line rounded-2xl bg-humo-100 px-4 py-3 text-[15px] leading-relaxed text-grafito-600">{{ $contact->message }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 border-t border-humo-200 bg-humo-50 px-6 py-3">
                            <button type="button" wire:click="toggleAnswered({{ $contact->id }})" class="ui-btn {{ $contact->answered_at ? 'ui-btn-ghost' : 'ui-btn-success' }} ui-btn-sm">
                                @unless ($contact->answered_at)<x-site.icon name="check" class="h-4 w-4" />@endunless {{ $contact->answered_at ? 'Marcar como pendiente' : 'Ya respondí' }}
                            </button>
                            <button type="button" wire:click="deleteContact({{ $contact->id }})" wire:confirm="¿Eliminar el mensaje de {{ $contact->name }}? No se puede deshacer." class="ui-btn ui-btn-ghost ui-btn-sm sm:ml-auto">Eliminar</button>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $contacts->links() }}
        @endif
    </x-ui.panel>
</div>
