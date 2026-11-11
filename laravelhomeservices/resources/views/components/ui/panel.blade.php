{{-- Marco de los paneles (admin, proveedor, cliente): menú del rol a la izquierda (arriba en móvil, deslizable),
     título + una frase que explica qué se hace en la página, botones de la página y avisos.
     Los números junto al menú son cosas pendientes (pagos por revisar, solicitudes sin responder).
     Uso: <x-ui.panel title="Mis servicios" help="…"><x-slot:actions>…</x-slot:actions> contenido </x-ui.panel> --}}
@props(['title', 'accent' => null, 'help' => null])
@php
    $user = auth()->user();
    $role = $user?->utype;

    if ($role === 'ADM') {
        $roleLabel = 'Administración';
        $pendingRequests = \App\Models\ServiceRequest::where('status', \App\Models\ServiceRequest::PENDING)->count();
        // [etiqueta, ruta, ícono, rutas activas, pendientes, sección]
        $items = [
            ['Inicio', 'admin.dashboard', 'home', ['admin.dashboard'], 0, 'Día a día'],
            ['Solicitudes', 'admin.service_requests', 'inbox', ['admin.service_requests'], $pendingRequests, 'Día a día'],
            ['Mensajes', 'admin.contacts', 'mail', ['admin.contacts'], \App\Models\Contact::whereNull('answered_at')->count(), 'Día a día'],
            ['Pagos Pro', 'admin.payments', 'card', ['admin.payments'], \App\Models\SubscriptionPayment::where('status', \App\Models\SubscriptionPayment::PENDING)->count(), 'Día a día'],
            ['Verificaciones', 'admin.verifications', 'shield', ['admin.verifications'], \App\Models\ProviderDocument::where('status', \App\Models\ProviderDocument::PENDING)->count(), 'Día a día'],
            ['Usuarios', 'admin.users', 'users', ['admin.users'], 0, 'Personas'],
            ['Proveedores', 'admin.service_providers', 'user', ['admin.service_providers'], 0, 'Personas'],
            ['Servicios', 'admin.all_services', 'list', ['admin.all_services', 'admin.add_service', 'admin.edit_service', 'admin.services_by_category'], 0, 'Catálogo'],
            ['Categorías', 'admin.service_categories', 'tag', ['admin.service_categories', 'admin.add_service_category', 'admin.edit_service_category'], 0, 'Catálogo'],
            ['Reseñas', 'admin.reviews', 'star', ['admin.reviews'], 0, 'Catálogo'],
            ['Preguntas frecuentes', 'admin.faq', 'question', ['admin.faq'], 0, 'Sitio'],
            ['Actividad', 'admin.activity', 'chart', ['admin.activity'], 0, 'Sitio'],
            ['Ajustes', 'admin.settings', 'settings', ['admin.settings'], 0, 'Sitio'],
        ];
    } elseif ($role === 'SVP') {
        $roleLabel = 'Panel de proveedor';
        $items = [
            ['Inicio', 'sprovider.dashboard', 'home', ['sprovider.dashboard'], 0],
            ['Solicitudes', 'sprovider.requests', 'inbox', ['sprovider.requests'], \App\Models\ServiceRequest::where('provider_id', $user->id)->where('status', \App\Models\ServiceRequest::PENDING)->count()],
            ['Mis servicios', 'sprovider.services', 'list', ['sprovider.services', 'sprovider.add_service', 'sprovider.edit_service'], 0],
            ['Mi perfil', 'sprovider.profile', 'user', ['sprovider.profile', 'sprovider.edit_profile'], 0],
            ['Verificarme', 'sprovider.verification', 'shield', ['sprovider.verification'], 0],
            ['Mi plan', 'sprovider.plan', 'card', ['sprovider.plan'], 0],
        ];
    } else {
        $roleLabel = 'Mi cuenta';
        $items = [
            ['Mis solicitudes', 'customer.dashboard', 'inbox', ['customer.dashboard'], 0],
            ['Buscar servicios', 'search', 'search', ['search'], 0],
        ];
    }
    $items[] = ['Cuenta y contraseña', 'profile.show', 'shield', ['profile.show'], 0, $role === 'ADM' ? 'Tu cuenta' : null];

    // En /livewire/update la ruta actual es la de Livewire: el menú se resuelve con la URL original de la página.
    $currentRoute = request()->route()?->getName();
    if (\Livewire\Livewire::isLivewireRequest()) {
        try {
            $currentRoute = app('router')->getRoutes()->match(\Illuminate\Http\Request::create(\Livewire\Livewire::originalUrl()))->getName();
        } catch (\Throwable) {
            $currentRoute = null;
        }
    }
@endphp
<div class="mx-auto max-w-[84rem] px-5 pb-20 pt-8 sm:px-8 lg:pt-12">
    <div class="lg:grid lg:grid-cols-[220px_minmax(0,1fr)] lg:gap-12">
        <aside class="mb-8 lg:mb-0">
            <div class="lg:sticky lg:top-28 lg:max-h-[calc(100vh-8rem)] lg:overflow-y-auto lg:pb-4 lg:[scrollbar-width:thin]">
                <p class="lp-eyebrow mb-3 hidden uppercase tracking-[.14em] text-grafito-400 lg:block">{{ $roleLabel }}</p>
                <nav aria-label="{{ $roleLabel }}" class="-mx-5 flex gap-1 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:-mx-8 sm:px-8 lg:mx-0 lg:flex-col lg:overflow-visible lg:px-0">
                    @foreach ($items as $item)
                        @php
                            [$label, $routeName, $icon, $active, $badge] = $item;
                            $section = $item[5] ?? null;
                            $isActive = in_array($currentRoute, $active, true);
                        @endphp
                        @if ($section !== null && $section !== ($items[$loop->index - 1][5] ?? null))
                            <p class="mb-1 mt-4 hidden px-4 text-[12px] font-medium uppercase tracking-[.12em] text-grafito-400 first:mt-0 lg:block">{{ $section }}</p>
                        @endif
                        <a href="{{ route($routeName) }}" @if ($isActive) aria-current="page" x-init="$el.parentElement.scrollLeft = $el.offsetLeft - 20" @endif
                           class="flex shrink-0 items-center gap-3 rounded-full px-4 py-2.5 text-[15px] transition lg:rounded-xl lg:py-2 {{ $isActive ? 'bg-noche-900 text-white' : 'text-grafito-600 hover:bg-humo-200 hover:text-grafito' }}">
                            <x-site.icon :name="$icon" class="h-[18px] w-[18px] shrink-0 {{ $isActive ? 'text-oro-300' : 'text-grafito-400' }}" />
                            <span class="whitespace-nowrap lg:flex-1">{{ $label }}</span>
                            @if ($badge > 0)
                                <span class="grid min-w-[22px] place-items-center rounded-full bg-oro-400 px-1.5 py-0.5 text-[12px] font-medium text-noche-900" title="{{ $badge }} pendiente(s)">{{ $badge }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="lp-title text-[2.25rem] sm:text-5xl">{{ $title }}@if ($accent) <span class="lp-accent">{{ $accent }}</span>@endif</h1>
                    @if ($help)
                        <p class="mt-3 max-w-2xl text-[16px] leading-relaxed text-grafito-500">{{ $help }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="flex shrink-0 flex-wrap gap-3">{{ $actions }}</div>
                @endisset
            </header>

            <x-ui.flash class="mt-8" />

            <div class="mt-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
