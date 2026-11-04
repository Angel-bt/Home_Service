@props(['value'])

<label {{ $attributes->merge(['class' => 'ui-label !mb-0']) }}>
    {{ $value ?? $slot }}
</label>
