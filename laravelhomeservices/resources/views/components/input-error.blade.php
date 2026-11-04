@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'ui-error']) }}><x-site.icon name="alert" class="mt-px h-4 w-4 shrink-0" /> {{ $message }}</p>
@enderror
