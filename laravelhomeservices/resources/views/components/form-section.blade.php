@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-8']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:col-span-2 md:mt-0">
        <form wire:submit="{{ $submit }}" class="ui-card overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <div class="flex items-center justify-end gap-3 border-t border-humo-200 bg-humo-50 px-6 py-4">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>
