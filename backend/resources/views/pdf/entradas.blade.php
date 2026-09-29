<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Entradas {{ $compra->factura->numero_factura }}</title>

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

    .cabecera { width: 100%; margin-bottom: 25px; }
    .cabecera td { vertical-align: top; }
    .derecha { text-align: right; }

    .titulo-documento {
      font-size: 16px;
      font-weight: bold;
    }

    .etiqueta {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #777;
      margin-bottom: 4px;
    }

    {{-- Cada entrada se dibuja como un bloque independiente, de modo que el
         documento se pueda recortar por entradas --}}
    table.entrada {
      width: 100%;
      border: 1px solid #333;
      border-collapse: collapse;
      margin-bottom: 14px;
    }

    table.entrada td {
      vertical-align: top;
      padding: 12px;
    }

    td.localizador {
      width: 38%;
      border-left: 1px dashed #999;
      text-align: center;
    }

    .codigo {
      font-size: 14px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    .aviso {
      margin-top: 20px;
      font-size: 9px;
      color: #777;
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
        {{ $emisor['email'] }}
      </td>
      <td class="derecha">
        <span class="titulo-documento">Entradas</span><br><br>
        Compra <strong>{{ $compra->factura->numero_factura }}</strong><br>
        Titular: {{ $titular }}
      </td>
    </tr>
  </table>

  {{-- Las líneas de la compra guardan una cantidad, pero aquí cada unidad se
       imprime por separado con el localizador que le corresponde --}}
  @foreach ($entradas as $entrada)
    <table class="entrada">
      <tr>
        <td>
          <div class="etiqueta">Evento</div>
          <strong>{{ $evento->nombre }}</strong><br>
          {{ \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y H:i') }}<br>
          {{ $evento->ubicacion }}<br><br>
          <div class="etiqueta">Tipo de entrada</div>
          {{ $entrada['tipo_entrada'] }}
        </td>
        <td class="localizador">
          <div class="etiqueta">Localizador</div>
          <span class="codigo">{{ $entrada['localizador'] }}</span><br><br>
          <span style="font-size: 9px; color: #777;">Entrada {{ $loop->iteration }} de {{ count($entradas) }}</span>
        </td>
      </tr>
    </table>
  @endforeach

  <p class="aviso">
    Cada entrada da derecho a un único acceso y es válida solo para el evento y la
    fecha indicados. El control de acceso corresponde al organizador del evento.
  </p>

  <div class="pie">
    {{ $emisor['nombre'] }} · {{ $emisor['email'] }}
  </div>

</body>

</html>
