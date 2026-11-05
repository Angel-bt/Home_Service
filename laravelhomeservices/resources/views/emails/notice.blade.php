@foreach ($lines as $line)
    <p>{{ $line }}</p>
@endforeach
@if ($actionLabel && $actionUrl)
    <p><a href="{{ $actionUrl }}">{{ $actionLabel }}</a></p>
@endif
<p>Malakplace</p>
