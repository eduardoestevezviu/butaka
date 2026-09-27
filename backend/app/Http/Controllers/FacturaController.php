<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Evento;
use App\Models\EstadoPago;
use App\Models\Factura;
use App\Models\TipoFactura;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class FacturaController extends Controller {

  // Series de facturación, una por destinatario. El número legal se compone de
  // la serie, la fecha de emisión y un correlativo que se reinicia cada día
  const SERIE_COMPRA = 'C';
  const SERIE_LIQUIDACION = 'L';

  // Facturación del organizador: las liquidaciones de sus eventos, con lo que le
  // corresponde una vez descontada la comisión
  public function facturacion(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $consulta = Factura::where('id_usuario', $request->user()->id_usuario)
        ->where('id_tipo_factura', TipoFactura::TIPO_LIQUIDACION)
        ->with('evento:id_evento,nombre,fecha')
        ->orderBy('fecha_emision', 'desc');

      // Filtra por evento si se pide
      if ($request->has('evento')) {
        $consulta->where('id_evento', $request->evento);
      }

      $liquidaciones = [];
      $totales = [
        'importe_bruto' => 0,
        'comision' => 0,
        'neto' => 0,
      ];

      foreach ($consulta->get() as $factura) {
        // Lo que recibe el organizador es la venta menos lo que factura Butaka,
        // que es la comisión con su IVA
        $neto = $factura->importe_bruto - $factura->total;

        $liquidaciones[] = [
          'id_factura' => $factura->id_factura,
          'numero_factura' => $factura->numero_factura,
          'fecha_emision' => $factura->fecha_emision,
          'evento' => $factura->evento,
          'importe_bruto' => $factura->importe_bruto,
          'porcentaje_comision' => $factura->comision,
          'base_imponible' => $factura->base_imponible,
          'iva' => $factura->iva,
          'total_comision' => $factura->total,
          'neto' => round($neto, 2),
        ];

        $totales['importe_bruto'] = $totales['importe_bruto'] + $factura->importe_bruto;
        $totales['comision'] = $totales['comision'] + $factura->total;
        $totales['neto'] = $totales['neto'] + $neto;
      }

      $totales['importe_bruto'] = round($totales['importe_bruto'], 2);
      $totales['comision'] = round($totales['comision'], 2);
      $totales['neto'] = round($totales['neto'], 2);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData([
        'liquidaciones' => $liquidaciones,
        'totales' => $totales,
      ]);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Detalle de una factura con su desglose, para la pantalla y para el PDF
  public function show(Request $request, string $id): JsonResponse {
    $result = new ResultResponse();
    try {
      $factura = $this->cargarFacturaCompleta($id);

      // La factura se localiza por un identificador de la URL, así que hay que
      // comprobar que el destinatario es quien la está pidiendo
      if ($factura->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($factura);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      $result->setStatusCode(ResultResponse::HTTP_NOT_FOUND);
      $result->setMessage(ResultResponse::MSG_NOT_FOUND);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Emite el comprobante del asistente. No es un endpoint: lo invoca el
  // PagoController dentro de la transacción que confirma el pago, porque la
  // factura y la compra pagada tienen que nacer o fallar juntas
  // Genera el documento PDF de la factura. Devuelve el fichero en lugar de un
  // JSON, así que no usa ResultResponse
  public function descargar(Request $request, string $id): Response|JsonResponse {
    $result = new ResultResponse();
    try {
      $factura = $this->cargarFacturaCompleta($id);

      if ($factura->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      $esLiquidacion = $factura->id_tipo_factura === TipoFactura::TIPO_LIQUIDACION;

      // El descuento por puntos no se guarda en la factura: se deduce de la
      // diferencia entre lo que suman las entradas y lo que se pagó
      $descuentoPuntos = 0;
      if (!$esLiquidacion) {
        $suma = 0;
        foreach ($factura->compra->detalleCompras as $linea) {
          $suma = $suma + $linea->precio_unitario * $linea->cantidad;
        }
        $descuentoPuntos = round($suma - $factura->total, 2);
      }

      // Quien presta el servicio al asistente es el organizador del evento, así
      // que la factura de compra deja constancia de que Butaka la expide en su
      // nombre. En la liquidación es Butaka quien factura su propia comisión
      $organizador = null;

      if ($esLiquidacion) {
        $destinatario = $factura->usuario->organizador->nombre_comercial;
        $nifDestinatario = $factura->usuario->organizador->nif;
        $tipoIva = config('butaka.iva_comision');
      } else {
        $destinatario = $factura->usuario->nombre . ' ' . $factura->usuario->apellidos;
        $nifDestinatario = null;
        $tipoIva = config('butaka.iva_entradas');
        $organizador = $factura->compra->detalleCompras->first()->tipoEntrada->evento->organizador;
      }

      $pdf = Pdf::loadView('pdf.factura', [
        'factura' => $factura,
        'emisor' => config('butaka.emisor'),
        'esLiquidacion' => $esLiquidacion,
        'destinatario' => $destinatario,
        'nifDestinatario' => $nifDestinatario,
        'tipoIva' => $tipoIva,
        'descuentoPuntos' => $descuentoPuntos,
        'organizador' => $organizador,
      ]);

      return $pdf->download($factura->numero_factura . '.pdf');
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      $result->setStatusCode(ResultResponse::HTTP_NOT_FOUND);
      $result->setMessage(ResultResponse::MSG_NOT_FOUND);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Carga la factura con todo lo que necesitan tanto el detalle como el PDF
  private function cargarFacturaCompleta(string $id): Factura {
    return Factura::with([
      'tipoFactura:id_tipo_factura,tipo_factura',
      'usuario:id_usuario,nombre,apellidos,email,calle,vivienda,ciudad,codigo_postal',
      'usuario.organizador:id_usuario,nombre_comercial,nif',
      'compra:id_compra,importe_total,puntos_canjeados,fecha_pago',
      'compra.detalleCompras.tipoEntrada:id_tipo_entrada,id_evento,nombre,precio',
      'compra.detalleCompras.tipoEntrada.evento:id_evento,nombre,fecha,id_usuario',
      'compra.detalleCompras.tipoEntrada.evento.organizador:id_usuario,nombre_comercial,nif',
      'evento:id_evento,nombre,fecha',
    ])->findOrFail($id);
  }

  public function emitirDeCompra(Compra $compra): Factura {
    $tipoIva = config('butaka.iva_entradas');

    // El asistente paga el precio con el IVA ya incluido, de modo que la base
    // se obtiene deshaciendo el impuesto y no sumándolo
    $total = $compra->importe_total;
    $base = round($total / (1 + $tipoIva / 100), 2);
    $iva = round($total - $base, 2);

    return Factura::create([
      'numero_factura' => $this->siguienteNumero(self::SERIE_COMPRA),
      'id_tipo_factura' => TipoFactura::TIPO_COMPRA,
      'id_usuario' => $compra->id_usuario,
      'fecha_emision' => now()->toDateString(),
      'base_imponible' => $base,
      'iva' => $iva,
      'total' => $total,
      'id_compra' => $compra->id_compra,
    ]);
  }

  // Emite la liquidación del organizador una vez celebrado el evento, momento en
  // que la comisión deja de poder revertirse por una cancelacion
  public function emitirDeLiquidacion(Evento $evento): Factura {
    $porcentaje = config('butaka.comision');
    $tipoIva = config('butaka.iva_comision');

    $bruto = $this->recaudacion($evento);

    // Se guarda el porcentaje aplicado, para que un cambio de tarifa en el
    // futuro no altere el importe de las liquidaciones ya emitidas
    $base = round($bruto * $porcentaje / 100, 2);
    $iva = round($base * $tipoIva / 100, 2);

    return Factura::create([
      'numero_factura' => $this->siguienteNumero(self::SERIE_LIQUIDACION),
      'id_tipo_factura' => TipoFactura::TIPO_LIQUIDACION,
      'id_usuario' => $evento->id_usuario,
      'fecha_emision' => now()->toDateString(),
      'base_imponible' => $base,
      'iva' => $iva,
      'total' => round($base + $iva, 2),
      'id_evento' => $evento->id_evento,
      'importe_bruto' => $bruto,
      'comision' => $porcentaje,
    ]);
  }

  // Recaudación del evento, contando solo las compras efectivamente pagadas. Las
  // pendientes no han generado ingreso y las reembolsadas lo han devuelto
  private function recaudacion(Evento $evento): float {
    $lineas = DetalleCompra::whereHas('tipoEntrada', function ($consulta) use ($evento) {
        $consulta->where('id_evento', $evento->id_evento);
      })
      ->whereHas('compra', function ($consulta) {
        $consulta->where('id_estado_pago', EstadoPago::ESTADO_COMPLETADO);
      })
      ->get();

    $bruto = 0;
    foreach ($lineas as $linea) {
      $bruto = $bruto + ($linea->cantidad * $linea->precio_unitario);
    }

    return round($bruto, 2);
  }

  // Calcula el siguiente número legal de la serie. Debe llamarse siempre dentro
  // de una transacción, porque lockForUpdate solo bloquea mientras está abierta.
  // Sin el bloqueo, dos compras simultáneas leerían el mismo máximo, generarían
  // el mismo número y la segunda chocaría con el índice único de la columna
  private function siguienteNumero(string $serie): string {
    $fecha = now()->format('Ymd');
    $prefijo = $serie . '-' . $fecha . '-';

    // El correlativo se reinicia cada día, de modo que el bloqueo recae sobre
    // las facturas de hoy y no sobre todo el histórico de la serie
    $ultimo = Factura::withTrashed()
      ->where('numero_factura', 'like', $prefijo . '%')
      ->lockForUpdate()
      ->max('numero_factura');

    if ($ultimo === null) {
      $siguiente = 1;
    } else {
      $siguiente = (int) substr($ultimo, strlen($prefijo)) + 1;
    }

    return $prefijo . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
  }
}
