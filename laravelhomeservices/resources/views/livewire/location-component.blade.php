<div>
    <div class="col-md-6">
        <ul class="visible-md visible-lg text-right">
            @if (Session::has('city'))
                <li class="no-wrap"><a href="{{ route('home.change_location') }}"><i class="fa fa-map-marker"></i> {{ Session::get('city') }}, {{ Session::get('state') }}</a></li>
            @else
                <li class="no-wrap"><a href="{{ route('home.change_location') }}"><i class="fa fa-map-marker"></i> Loja, Malacatos</a></li>
            @endif
        </ul>
    </div>
</div>