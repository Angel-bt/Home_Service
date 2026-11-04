<div>
    <x-ui.panel title="Mensajes" help="Lo que la gente escribe desde la página de contacto, del más nuevo al más antiguo. Respóndeles por correo o por WhatsApp.">
        @if ($contacts->isEmpty())
            <x-ui.empty icon="mail" title="Todavía no hay mensajes">Cuando alguien escriba desde la página de contacto, lo verás aquí.</x-ui.empty>
        @else
            <div class="space-y-3">
                @foreach ($contacts as $contact)
                    @php $wa = \App\Support\WhatsApp::link((string) $contact->phone, 'Hola ' . $contact->name . ', te escribimos de Malakplace por tu mensaje.'); @endphp
                    <article wire:key="contact-{{ $contact->id }}" class="ui-card p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-[17px] font-medium">{{ $contact->name }}</h2>
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
                    </article>
                @endforeach
            </div>
            {{ $contacts->links() }}
        @endif
    </x-ui.panel>
</div>
