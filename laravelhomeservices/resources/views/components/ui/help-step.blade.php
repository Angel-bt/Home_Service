{{-- Paso dentro de <x-ui.help>. --}}
@props(['n'])
<li class="flex gap-3">
    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-noche-900 text-[12px] font-medium text-white">{{ $n }}</span>
    <span>{{ $slot }}</span>
</li>
