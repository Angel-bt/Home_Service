{{-- Sellos vigentes de un proveedor, con su fecha. Sin sellos no muestra nada: son voluntarios y su ausencia no dice nada de la persona. --}}
@props(['provider', 'note' => true])
@php $badges = $provider?->trustBadges() ?? []; @endphp
@if ($badges)
    <div {{ $attributes }}>
        <ul class="space-y-2">
            @foreach ($badges as $badge)
                <li class="flex items-start gap-2.5 text-[15px]">
                    <x-site.icon name="shield" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-700" />
                    <span>
                        <span class="font-medium">{{ $badge['label'] }}</span>
                        <span class="block text-[13px] text-grafito-500">{{ $badge['detail'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
        @if ($note)
            <p class="mt-3 text-[13px] leading-snug text-grafito-500">Los sellos son voluntarios. Dicen que revisamos un documento en esa fecha; no son una garantía. <a href="{{ route('home.terms') }}#sellos" class="ui-link">Qué significan</a></p>
        @endif
    </div>
@endif
