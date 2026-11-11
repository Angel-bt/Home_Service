<div>
    {{-- Las preguntas las edita la administración en /admin/faq. --}}
    <x-ui.page title="Preguntas" accent="frecuentes" eyebrow="Ayuda" lede="Si no encuentras tu respuesta, escríbenos por WhatsApp y te ayudamos." />

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="grid gap-12 lg:grid-cols-[220px_1fr]">
            @foreach ($faqs as $group => $items)
                <h2 class="lp-eyebrow uppercase tracking-[.14em] text-grafito-400 lg:pt-6">{{ $group }}</h2>
                <div class="-mt-8 lg:mt-0">
                    @foreach ($items as $question => $answer)
                        <details class="group border-t border-humo-300 last:border-b">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6 text-[18px] font-medium [&::-webkit-details-marker]:hidden">
                                {{ $question }}
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full border border-humo-300 transition group-open:rotate-45"><x-site.icon name="plus" class="h-4 w-4" /></span>
                            </summary>
                            <p class="max-w-2xl pb-6 text-[16px] leading-relaxed text-grafito-600">{{ $answer }}</p>
                        </details>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="mt-16 flex flex-wrap gap-3">
            <a href="{{ route('home.contact') }}" class="ui-btn ui-btn-primary">Escribirnos</a>
            <a href="{{ route('search') }}" class="ui-btn ui-btn-secondary">Buscar servicios</a>
        </div>
    </section>
</div>
