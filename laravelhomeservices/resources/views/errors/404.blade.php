@extends('errors.layout')
@section('code', '404')
@section('heading', 'No encontramos')
@section('accent', 'esta página')
@section('text', 'Puede que el enlace esté mal escrito o que ya no exista. Desde el inicio puedes buscar lo que necesitas.')
@section('actions')
    <a href="{{ url('/') }}" class="ui-btn ui-btn-primary ui-btn-lg">Ir al inicio</a>
    <a href="{{ route('search') }}" class="ui-btn ui-btn-secondary ui-btn-lg">Buscar servicios</a>
@endsection
