<div>
    <x-ui.panel title="Diapositivas" help="Esta función viene del diseño anterior: la portada actual no muestra diapositivas, así que por ahora no hace falta usarla.">
        <x-slot:actions>
            <a href="{{ route('admin.add_slide') }}" class="ui-btn ui-btn-secondary"><x-site.icon name="plus" class="h-4 w-4" /> Agregar diapositiva</a>
        </x-slot:actions>

        @if ($slides->isEmpty())
            <x-ui.empty icon="image" title="No hay diapositivas">No necesitas agregar ninguna: la portada no las usa.</x-ui.empty>
        @else
            <ul class="space-y-3">
                @foreach ($slides as $slide)
                    <li wire:key="slide-{{ $slide->id }}" class="ui-card flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                        <img src="{{ \App\Support\Media::url('slider', $slide->image) }}" alt="" loading="lazy" class="h-20 w-full shrink-0 rounded-2xl object-cover sm:w-32">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-[17px] font-medium">{{ $slide->title }}</h2>
                            <div class="mt-1"><x-ui.status :status="$slide->status ? 'approved' : 'cancelled'" :label="$slide->status ? 'Activa' : 'Inactiva'" /></div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.edit_slide', ['slide_id' => $slide->id]) }}" class="ui-btn ui-btn-secondary ui-btn-sm"><x-site.icon name="edit" class="h-4 w-4" /> Editar</a>
                            <button type="button" wire:click="deleteSlide({{ $slide->id }})" wire:confirm="¿Eliminar esta diapositiva? No se puede deshacer." class="ui-btn ui-btn-danger ui-btn-sm"><x-site.icon name="trash" class="h-4 w-4" /> Eliminar</button>
                        </div>
                    </li>
                @endforeach
            </ul>
            {{ $slides->links() }}
        @endif
    </x-ui.panel>
</div>
