{{-- Pie de página. --}}
@php
    $phone = config('marketplace.contact_phone');
    $email = config('marketplace.contact_email');
    $whatsapp = \App\Support\WhatsApp::link($phone, 'Hola, tengo una consulta sobre Malakplace.');
@endphp
<footer data-tone="dark" class="relative overflow-hidden bg-noche-950 text-white">
    <div class="mx-auto max-w-[84rem] px-5 pt-20 sm:px-8">
        <div class="grid gap-12 border-b border-white/10 pb-16 md:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr]">
            <div>
                <p class="lp-title text-3xl text-white">¿Una duda? <span class="lp-accent text-oro-300">Escríbenos.</span></p>
                <p class="mt-4 max-w-xs text-[15px] leading-relaxed text-white/50">Atendemos desde Malacatos. Respondemos el mismo día.</p>
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-[13px] font-medium uppercase tracking-[.08em] text-noche-900 transition hover:bg-oro-300">
                        <x-site.icon name="whatsapp" class="h-4 w-4" /> WhatsApp
                    </a>
                @endif
            </div>
            <div>
                <p class="text-[13px] uppercase tracking-[.08em] text-white/40">Zonas</p>
                <ul class="mt-5 space-y-2.5 text-[15px]">
                    @foreach (config('marketplace.zones') as $slug => $label)
                        <li><a href="{{ route('search', ['zona' => $slug]) }}" class="text-white/75 transition hover:text-white">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-[13px] uppercase tracking-[.08em] text-white/40">Malakplace</p>
                <ul class="mt-5 space-y-2.5 text-[15px]">
                    <li><a href="{{ route('home.service_categories') }}" class="text-white/75 hover:text-white">Todos los oficios</a></li>
                    <li><a href="{{ route('home.about') }}" class="text-white/75 hover:text-white">Nosotros</a></li>
                    <li><a href="{{ route('home.faq') }}" class="text-white/75 hover:text-white">Preguntas frecuentes</a></li>
                    <li><a href="{{ route('home.contact') }}" class="text-white/75 hover:text-white">Contacto</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[13px] uppercase tracking-[.08em] text-white/40">Contacto</p>
                <ul class="mt-5 space-y-2.5 text-[15px] text-white/75">
                    <li>Malacatos, Loja · Ecuador</li>
                    @if ($phone)<li><a href="tel:{{ $phone }}" class="hover:text-white">{{ $phone }}</a></li>@endif
                    @if ($email)<li><a href="mailto:{{ $email }}" class="break-all hover:text-white">{{ $email }}</a></li>@endif
                </ul>
            </div>
        </div>
        <div class="flex flex-col gap-3 py-7 text-[13px] text-white/40 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Malakplace, un proyecto de Proyetech · Fotos: Unsplash</p>
            <p class="flex gap-6"><a href="{{ route('home.terms') }}" class="hover:text-white">Términos</a><a href="{{ route('home.privacy') }}" class="hover:text-white">Privacidad</a></p>
        </div>
    </div>
    {{-- Marca grande al pie, recortada --}}
    <p class="lp-title pointer-events-none -mb-[4vw] select-none whitespace-nowrap text-center text-[22vw] leading-none text-white/[.04]" aria-hidden="true">Malakplace</p>
</footer>
