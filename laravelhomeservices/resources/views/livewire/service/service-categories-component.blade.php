<div>
    <x-ui.page title="Todos los" accent="oficios" eyebrow="Catálogo"
               lede="Elige el oficio que necesitas o búscalo por nombre.">
        <form action="{{ route('search') }}" method="GET" role="search" class="flex max-w-xl gap-2">
            <label class="relative flex-1">
                <span class="sr-only">¿Qué necesitas?</span>
                <x-site.icon name="search" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-grafito-400" />
                <input type="search" name="q" class="ui-input pl-11" placeholder="Ej.: gasfitero">
            </label>
            <button type="submit" class="ui-btn ui-btn-primary">Buscar</button>
        </form>
    </x-ui.page>

    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        @if ($scategories->isEmpty())
            <x-ui.empty icon="tag" title="Aún no hay oficios">Vuelve pronto: estamos sumando proveedores del valle.</x-ui.empty>
        @else
            <div class="grid md:grid-cols-2 md:gap-x-16">
                @foreach ($scategories as $i => $scategory)
                    <a href="{{ route('home.services_by_category', ['category_slug' => $scategory->slug]) }}"
                       class="group flex items-center gap-5 border-t border-humo-300 py-6 {{ $loop->last ? 'border-b' : ($loop->remaining === 1 && $loop->count % 2 === 0 ? 'md:border-b' : '') }}">
                        <span class="w-8 text-[13px] tabular-nums text-grafito-400">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="lp-title flex-1 text-2xl transition duration-300 group-hover:translate-x-2">{{ $scategory->name }}</span>
                        <span class="hidden text-[13px] text-grafito-500 sm:inline">{{ $scategory->services_count > 0 ? $scategory->services_count . ' ' . ($scategory->services_count === 1 ? 'servicio' : 'servicios') : 'Próximamente' }}</span>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-humo-300 transition duration-300 group-hover:border-noche-900 group-hover:bg-noche-900 group-hover:text-white">
                            <x-site.icon name="arrow-up-right" class="h-4 w-4" />
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
