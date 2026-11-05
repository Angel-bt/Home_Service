<div>
    @php
        $user = auth()->user();
        $missing = array_filter([
            ! $sprovider?->whatsapp ? 'tu WhatsApp' : null,
            (! $sprovider || $sprovider->zones->isEmpty()) ? 'las zonas donde trabajas' : null,
        ]);
        $rows = [
            ['Correo', $user->email],
            ['Teléfono', $user->phone ?: '—'],
            ['WhatsApp', $sprovider?->whatsapp ?: 'Sin configurar'],
            ['Zonas donde trabajas', $sprovider && $sprovider->zones->isNotEmpty() ? $sprovider->zones->map->label()->implode(', ') : 'Sin configurar'],
            ['Ciudad o parroquia', $sprovider?->city ?: '—'],
            ['Oficio principal', $sprovider?->category?->name ?? '—'],
            ['Años de experiencia', $sprovider?->experience_years ?? '—'],
            ['Referencia de ubicación', $sprovider?->service_location ?: '—'],
        ];
    @endphp
    <x-ui.panel title="Mi perfil" help="Esto es lo que los clientes ven de ti junto a tus servicios.">
        <x-slot:actions>
            <a href="{{ route('sprovider.edit_profile') }}" class="ui-btn ui-btn-primary"><x-site.icon name="edit" class="h-4 w-4" /> Editar perfil</a>
        </x-slot:actions>

        @if ($missing)
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-[15px] text-amber-950" role="status">
                <x-site.icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-amber-700" />
                <p>Te falta poner {{ implode(' y ', $missing) }}. Sin eso los clientes no pueden encontrarte ni escribirte.</p>
            </div>
        @endif

        <div class="ui-card ui-card-pad grid gap-8 md:grid-cols-[200px_1fr]">
            <div>
                <img src="{{ \App\Support\Media::url('sproviders', ($sprovider?->image ?: 'default.jpg')) }}" alt="Tu foto de perfil" class="aspect-square w-full max-w-[200px] rounded-3xl object-cover">
                @unless ($sprovider?->image)
                    <p class="ui-hint">Aún no subes tu foto.</p>
                @endunless
            </div>
            <div>
                <h2 class="lp-title text-3xl">{{ $user->name }}</h2>
                <p class="mt-3 text-[16px] leading-relaxed text-grafito-600">{{ $sprovider?->about ?: 'Aún no has escrito una presentación. Cuenta tu experiencia en dos líneas: ayuda a que te elijan.' }}</p>
                <dl class="mt-6 divide-y divide-humo-200 border-t border-humo-200 text-[15px]">
                    @foreach ($rows as [$label, $value])
                        <div class="flex flex-col gap-0.5 py-3 sm:flex-row sm:gap-6">
                            <dt class="text-grafito-500 sm:w-52 sm:shrink-0">{{ $label }}</dt>
                            <dd class="min-w-0 break-words {{ $value === 'Sin configurar' ? 'font-medium text-amber-800' : '' }}">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="mt-6 border-t border-humo-200 pt-6">
                    @if ($sprovider?->trustBadges())
                        <x-ui.trust-badges :provider="$sprovider" :note="false" />
                    @else
                        <p class="text-[15px] text-grafito-500">Aún no tienes sellos. Son voluntarios y dan confianza a los clientes.</p>
                    @endif
                    <a href="{{ route('sprovider.verification') }}" class="ui-link mt-3 inline-block text-[15px]">Ver mi verificación</a>
                </div>
            </div>
        </div>
    </x-ui.panel>
</div>
