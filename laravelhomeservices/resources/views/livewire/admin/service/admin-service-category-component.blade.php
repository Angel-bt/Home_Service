<div>
    <x-ui.panel title="Categorías" help="Los oficios que aparecen en el sitio. Cada servicio pertenece a uno.">
        <x-slot:actions>
            <a href="{{ route('admin.add_service_category') }}" class="ui-btn ui-btn-primary"><x-site.icon name="plus" class="h-4 w-4" /> Agregar categoría</a>
        </x-slot:actions>

        @if ($scategories->isEmpty())
            <x-ui.empty icon="tag" title="Todavía no hay categorías">
                Crea la primera (por ejemplo «Gasfitería») para que los proveedores puedan publicar.
                <x-slot:actions>
                    <a href="{{ route('admin.add_service_category') }}" class="ui-btn ui-btn-primary">Agregar categoría</a>
                </x-slot:actions>
            </x-ui.empty>
        @else
            <ul class="space-y-3">
                @foreach ($scategories as $scategory)
                    <li wire:key="category-{{ $scategory->id }}" class="ui-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <img src="{{ \App\Support\Media::url('categories', $scategory->image) }}" alt="" loading="lazy" class="h-14 w-14 shrink-0 rounded-xl bg-humo-100 object-contain p-1.5">
                        <div class="min-w-0 flex-1">
                            <h2 class="flex flex-wrap items-center gap-2 text-[17px] font-medium">
                                {{ $scategory->name }}
                                @if ($scategory->featured)<x-ui.status status="pending" label="Destacada" />@endif
                            </h2>
                            <p class="mt-0.5 text-[14px] text-grafito-500">{{ $scategory->services_count }} {{ $scategory->services_count === 1 ? 'servicio' : 'servicios' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.services_by_category', ['category_slug' => $scategory->slug]) }}" class="ui-btn ui-btn-ghost ui-btn-sm"><x-site.icon name="list" class="h-4 w-4" /> Sus servicios</a>
                            <a href="{{ route('admin.edit_service_category', ['category_id' => $scategory->id]) }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="edit" class="h-4 w-4" /> Editar</a>
                            <button type="button" wire:click="deleteServiceCategory({{ $scategory->id }})" wire:confirm="¿Eliminar «{{ $scategory->name }}»?{{ $scategory->services_count > 0 ? ' También se borrarán sus ' . $scategory->services_count . ' servicio(s).' : '' }} No se puede deshacer." class="ui-btn ui-btn-danger ui-btn-sm"><x-site.icon name="trash" class="h-4 w-4" /> Eliminar</button>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $scategories->links() }}
        @endif
    </x-ui.panel>
</div>
