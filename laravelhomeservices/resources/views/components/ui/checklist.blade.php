{{-- Primeros pasos ("qué hacer ahora"). Se oculta sola cuando todo está hecho.
     $items: [['done' => bool, 'title' => …, 'text' => …, 'action' => 'Texto del botón', 'href' => url], …] --}}
@props(['items', 'title' => 'Tus primeros pasos'])
@php
    $done = collect($items)->where('done', true)->count();
    $total = count($items);
    $next = collect($items)->search(fn ($item) => ! $item['done']);
@endphp
@if ($done < $total)
    <section {{ $attributes->merge(['class' => 'ui-card overflow-hidden']) }}>
        <div class="flex flex-col gap-4 border-b border-humo-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 class="lp-title text-2xl">{{ $title }}</h2>
                <p class="mt-1 text-[14px] text-grafito-500">Llevas {{ $done }} de {{ $total }}. Hazlos en orden; cada uno toma un par de minutos.</p>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-humo-200 sm:w-48" aria-hidden="true">
                <div class="h-full rounded-full bg-emerald-600" style="width: {{ $total ? round($done / $total * 100) : 0 }}%"></div>
            </div>
        </div>
        <ol>
            @foreach ($items as $i => $item)
                <li class="flex flex-col gap-4 border-b border-humo-200 px-6 py-5 last:border-b-0 sm:flex-row sm:items-center sm:px-8 {{ $i === $next ? 'bg-oro-300/15' : '' }}">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-[14px] font-medium {{ $item['done'] ? 'bg-emerald-600 text-white' : ($i === $next ? 'bg-noche-900 text-white' : 'border border-humo-300 text-grafito-400') }}">
                        @if ($item['done'])<x-site.icon name="check" class="h-5 w-5" />@else{{ $i + 1 }}@endif
                    </span>
                    <div class="flex-1">
                        <p class="text-[16px] font-medium {{ $item['done'] ? 'text-grafito-400 line-through decoration-grafito-400/40' : '' }}">{{ $item['title'] }}</p>
                        @unless ($item['done'])<p class="mt-0.5 text-[14px] leading-snug text-grafito-500">{{ $item['text'] }}</p>@endunless
                    </div>
                    @if (! $item['done'] && ! empty($item['href']))
                        <a href="{{ $item['href'] }}" class="ui-btn {{ $i === $next ? 'ui-btn-primary' : 'ui-btn-secondary' }} ui-btn-sm self-start sm:self-auto">{{ $item['action'] }} <x-site.icon name="arrow-right" class="h-4 w-4" /></a>
                    @endif
                </li>
            @endforeach
        </ol>
    </section>
@endif
