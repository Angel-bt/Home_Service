@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-[15px] text-red-900']) }} role="alert">
        <p class="font-medium">Revisa estos datos:</p>
        <ul class="mt-2 space-y-1 text-[14px]">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
