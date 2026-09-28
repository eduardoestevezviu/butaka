<x-mail::message>
# Compra confirmada

Hola {{ $usuario->nombre }}:

Hemos recibido tu pago y tus entradas para **{{ $evento->nombre }}** ya están reservadas a tu nombre. El evento se celebra el {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y \a \l\a\s H:i') }} en {{ $evento->ubicacion }}.

@foreach ($compra->detalleCompras as $detalle)
- {{ $detalle->cantidad }} x {{ $detalle->tipoEntrada->nombre }}
@endforeach

**Total abonado: {{ number_format($compra->importe_total, 2, ',', '.') }} €**

@if ($compra->puntos_ganados > 0)
Esta compra te ha aportado {{ $compra->puntos_ganados }} puntos, que podrás canjear por descuentos en próximas compras.
@endif

<x-mail::button :url="config('butaka.frontend_url') . '/compras'">
Ver mis compras
</x-mail::button>

Desde esa pantalla puedes consultar el detalle de la compra y descargar tu factura.

Gracias por tu confianza,<br>
{{ config('app.name') }}
</x-mail::message>
