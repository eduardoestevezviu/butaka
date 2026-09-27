<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>{{ $factura->numero_factura }}</title>

  {{-- dompdf solo entiende un subconjunto de CSS, así que se emplean tablas y
       propiedades clásicas en lugar de flex o grid --}}
  <style>
    @page { margin: 1.8cm; }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 11px;
      color: #222;
    }

    h1 {
      font-size: 20px;
      margin: 0;
    }

    .cabecera { width: 100%; margin-bottom: 30px; }
    .cabecera td { vertical-align: top; }
    .derecha { text-align: right; }

    .titulo-documento {
      font-size: 16px;
      font-weight: bold;
    }

    .partes { width: 100%; margin-bottom: 25px; }
    .partes td {
      width: 50%;
      vertical-align: top;
      padding-right: 15px;
    }

    .etiqueta {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #777;
      margin-bottom: 4px;
    }

    table.lineas {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    table.lineas th {
      text-align: left;
      border-bottom: 1px solid #333;
      padding: 6px 4px;
      font-size: 10px;
      text-transform: uppercase;
    }

    table.lineas td {
      padding: 6px 4px;
      border-bottom: 1px solid #eee;
    }

    table.totales {
      width: 45%;
      margin-left: 55%;
      border-collapse: collapse;
    }

    table.totales td { padding: 5px 4px; }

    table.totales tr.total td {
      border-top: 1px solid #333;
      font-weight: bold;
      font-size: 13px;
    }

    .pie {
      position: fixed;
      bottom: 0;
      width: 100%;
      font-size: 9px;
      color: #777;
      text-align: center;
    }
  </style>
</head>

<body>

  <table class="cabecera">
    <tr>
      <td>
        <h1>{{ $emisor['nombre'] }}</h1>
        {{ $emisor['nif'] }}<br>
        {{ $emisor['direccion'] }}<br>
        {{ $emisor['localidad'] }}<br>
        {{ $emisor['email'] }}
      </td>
      <td class="derecha">
        <span class="titulo-documento">{{ $esLiquidacion ? 'Factura de liquidación' : 'Factura' }}</span><br><br>
        <strong>{{ $factura->numero_factura }}</strong><br>
        Fecha de emisión: {{ \Carbon\Carbon::parse($factura->fecha_emision)->format('d/m/Y') }}
        @if ($organizador)
          <br><br>
          <span style="font-size: 10px; color: #555;">
            Expedida por {{ $emisor['nombre'] }} en nombre y por cuenta de<br>
            <strong>{{ $organizador->nombre_comercial }}</strong>, NIF {{ $organizador->nif }}
          </span>
        @endif
      </td>
    </tr>
  </table>

  <table class="partes">
    <tr>
      <td>
        <div class="etiqueta">Destinatario</div>
        <strong>{{ $destinatario }}</strong><br>
        @if ($nifDestinatario)
          {{ $nifDestinatario }}<br>
        @endif
        @if ($factura->usuario->calle)
          {{ $factura->usuario->calle }}{{ $factura->usuario->vivienda ? ', ' . $factura->usuario->vivienda : '' }}<br>
        @endif
        @if ($factura->usuario->ciudad)
          {{ $factura->usuario->codigo_postal }} {{ $factura->usuario->ciudad }}<br>
        @endif
        {{ $factura->usuario->email }}
      </td>
      <td>
        <div class="etiqueta">Concepto</div>
        @if ($esLiquidacion)
          Liquidación de la venta de entradas de<br>
          <strong>{{ $factura->evento->nombre }}</strong><br>
          celebrado el {{ \Carbon\Carbon::parse($factura->evento->fecha)->format('d/m/Y') }}
        @else
          Compra de entradas realizada el
          {{ \Carbon\Carbon::parse($factura->compra->fecha_pago)->format('d/m/Y') }}
        @endif
      </td>
    </tr>
  </table>

  @if ($esLiquidacion)

    {{-- En la liquidación, lo que se factura es la comisión de Butaka sobre lo
         recaudado, no las entradas --}}
    <table class="lineas">
      <tr>
        <th>Descripción</th>
        <th class="derecha">Importe</th>
      </tr>
      <tr>
        <td>Entradas vendidas del evento</td>
        <td class="derecha">{{ number_format($factura->importe_bruto, 2, ',', '.') }} €</td>
      </tr>
      <tr>
        <td>Comisión de intermediación ({{ $factura->comision }} %)</td>
        <td class="derecha">{{ number_format($factura->base_imponible, 2, ',', '.') }} €</td>
      </tr>
    </table>

  @else

    <table class="lineas">
      <tr>
        <th>Descripción</th>
        <th class="derecha">Cantidad</th>
        <th class="derecha">Precio</th>
        <th class="derecha">Importe</th>
      </tr>
      @foreach ($factura->compra->detalleCompras as $linea)
        <tr>
          <td>
            {{ $linea->tipoEntrada->evento->nombre }}<br>
            <span style="color: #777;">{{ $linea->tipoEntrada->nombre }}</span>
          </td>
          <td class="derecha">{{ $linea->cantidad }}</td>
          <td class="derecha">{{ number_format($linea->precio_unitario, 2, ',', '.') }} €</td>
          <td class="derecha">{{ number_format($linea->precio_unitario * $linea->cantidad, 2, ',', '.') }} €</td>
        </tr>
      @endforeach

      @if ($descuentoPuntos > 0)
        <tr>
          <td colspan="3">Descuento por canje de {{ $factura->compra->puntos_canjeados }} puntos</td>
          <td class="derecha">- {{ number_format($descuentoPuntos, 2, ',', '.') }} €</td>
        </tr>
      @endif
    </table>

  @endif

  <table class="totales">
    <tr>
      <td>Base imponible</td>
      <td class="derecha">{{ number_format($factura->base_imponible, 2, ',', '.') }} €</td>
    </tr>
    <tr>
      <td>IVA ({{ $tipoIva }} %)</td>
      <td class="derecha">{{ number_format($factura->iva, 2, ',', '.') }} €</td>
    </tr>
    <tr class="total">
      <td>Total</td>
      <td class="derecha">{{ number_format($factura->total, 2, ',', '.') }} €</td>
    </tr>
  </table>

  <div class="pie">
    {{ $emisor['nombre'] }} · {{ $emisor['nif'] }} · Documento generado electrónicamente
  </div>

</body>

</html>
