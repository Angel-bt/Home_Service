@extends('errors.layout')
@section('code', '419')
@section('heading', 'La página')
@section('accent', 'se venció')
@section('text', 'Estuvo abierta mucho tiempo y, por seguridad, hay que cargarla otra vez. Vuelve atrás, recarga la página e intenta de nuevo; lo que escribiste quizás deba llenarse otra vez.')
@section('actions')
    <a href="{{ url()->previous() }}" class="ui-btn ui-btn-primary ui-btn-lg">Volver e intentar de nuevo</a>
@endsection
