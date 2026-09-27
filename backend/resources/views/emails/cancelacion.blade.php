<x-mail::message>
# Evento cancelado

Hola {{ $usuario->nombre }}:

Lamentamos comunicarte que **{{ $evento->nombre }}**, previsto para el {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y \a \l\a\s H:i') }} en {{ $evento->ubicacion }}, ha sido cancelado por su organizador.

@if ($reembolsada)
El importe de tu compra ya se ha devuelto al mismo método de pago con el que la abonaste. Según tu banco, puede tardar unos días en reflejarse.
@else
Tu compra estaba pendiente de pago, así que no se te ha cobrado nada.
@endif

Los puntos que hubieras canjeado en esa compra vuelven a estar disponibles en tu cuenta.

<x-mail::button :url="config('butaka.frontend_url')">
Ver otros eventos
</x-mail::button>

Sentimos las molestias,<br>
{{ config('app.name') }}
</x-mail::message>
