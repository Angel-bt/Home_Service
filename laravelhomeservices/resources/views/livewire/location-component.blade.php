<div>
    <a href="{{ route('home.change_location') }}" class="inline-flex items-center gap-1.5 text-[14px] text-grafito-500 hover:text-grafito">
        <x-site.icon name="pin" class="h-4 w-4" /> {{ Session::has('city') ? Session::get('city') . ', ' . Session::get('state') : 'Loja, Malacatos' }}
    </a>
</div>
