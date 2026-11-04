{{-- Contador de visitas (componente heredado; los layouts ya registran la visita con fetch). --}}
<div class="inline-flex items-center gap-2 text-[13px] text-grafito-500" role="status">
    <x-site.icon name="chart" class="h-4 w-4" /> Visitas {{ $visits ?? 0 }}
</div>
