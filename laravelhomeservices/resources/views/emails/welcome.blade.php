<p>Hola {{ $user->name }}:</p>
<p>Tu cuenta en Malakplace ya está lista.</p>
@if ($user->utype === 'SVP')
    <p>Para empezar a recibir clientes, entra a tu panel y sigue los pasos: pon tu WhatsApp y tus zonas, sube tu foto y publica tu primer servicio.</p>
    <p><a href="{{ route('sprovider.dashboard') }}">Ir a mi panel</a></p>
@else
    <p>Busca lo que necesitas y escríbele directo a quien lo hace.</p>
    <p><a href="{{ route('search') }}">Buscar servicios</a></p>
@endif
<p>¿Dudas? Responde a este correo o escríbenos por WhatsApp.</p>
