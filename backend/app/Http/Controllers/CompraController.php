<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Compra;
use App\Models\DetalleCarrito;
use App\Models\DetalleCompra;
use App\Models\EstadoEvento;
use App\Models\EstadoPago;
use App\Models\Evento;
use App\Models\TipoEntrada;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CompraController extends Controller {

  // Listado de compras del asistente autenticado
  public function index(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      // El listado es ligero, el detalle de cada compra se pide al desplegarla
      $compras = Compra::where('id_usuario', $request->user()->id_usuario)
        ->with([
          'estadoPago:id_estado_pago,estado_pago',
          'detalleCompras:id_compra,id_tipo_entrada,cantidad',
          'detalleCompras.tipoEntrada:id_tipo_entrada,id_evento',
          'detalleCompras.tipoEntrada.evento:id_evento,nombre,fecha,imagen',
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(config('butaka.compras_por_pagina'));

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($compras);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function show(Request $request, Compra $compra): JsonResponse {
    $result = new ResultResponse();
    try {
      // Un asistente solo puede consultar sus propias compras
      if ($compra->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      $compra->load([
        'estadoPago:id_estado_pago,estado_pago',
        'factura',
        'detalleCompras.tipoEntrada:id_tipo_entrada,nombre,id_evento',
        'detalleCompras.tipoEntrada.evento:id_evento,nombre,fecha,ubicacion,imagen',
      ]);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($compra);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Documento con las entradas de una compra. Cada unidad adquirida se imprime
  // por separado y se identifica con un localizador propio
  public function entradas(Request $request, Compra $compra): Response|JsonResponse {
    $result = new ResultResponse();
    try {
      // Un asistente solo puede descargar las entradas de sus propias compras
      if ($compra->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      // Solo se emiten las entradas de una compra pagada. Una compra pendiente
      // tiene el aforo reservado, pero todavía puede no llegar a pagarse, y una
      // reembolsada ha perdido el derecho de acceso
      if ($compra->id_estado_pago !== EstadoPago::ESTADO_COMPLETADO) {
        $result->setStatusCode(ResultResponse::HTTP_CONFLICT);
        $result->setMessage('La compra no está pagada');
        return $result->toResponse();
      }

      $compra->load([
        'factura:id_factura,id_compra,numero_factura',
        'detalleCompras.tipoEntrada:id_tipo_entrada,id_evento,nombre',
        'detalleCompras.tipoEntrada.evento:id_evento,nombre,fecha,ubicacion',
      ]);

      $pdf = Pdf::loadView('pdf.entradas', [
        'compra' => $compra,
        'emisor' => config('butaka.emisor'),
        // El titular es quien hizo la compra, que es el usuario autenticado, ya
        // comprobado más arriba
        'titular' => $request->user()->nombre . ' ' . $request->user()->apellidos,
        'evento' => $compra->detalleCompras->first()->tipoEntrada->evento,
        'entradas' => $this->desglosarEntradas($compra),
      ]);

      return $pdf->download('entradas-' . $compra->factura->numero_factura . '.pdf');
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Convierte las líneas de la compra, que guardan una cantidad, en una entrada
  // por unidad. El localizador se compone del número de factura y un correlativo
  // dentro de la compra, de modo que identifica cada entrada de forma única
  private function desglosarEntradas(Compra $compra): array {
    $entradas = [];
    $numero = 0;

    // Se recorren las líneas en un orden fijo para que el correlativo que le
    // corresponde a cada entrada no dependa de cómo las devuelva la consulta
    foreach ($compra->detalleCompras->sortBy('id_tipo_entrada') as $linea) {
      for ($unidad = 1; $unidad <= $linea->cantidad; $unidad++) {
        $numero = $numero + 1;

        $entradas[] = [
          'localizador' => $compra->factura->numero_factura . '-' . str_pad($numero, 2, '0', STR_PAD_LEFT),
          'tipo_entrada' => $linea->tipoEntrada->nombre,
        ];
      }
    }

    return $entradas;
  }

  // Convierte en una compra pendiente de pago las entradas que el asistente
  // tiene en el carrito de un evento concreto
  public function store(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $datos = $this->validarCompra($request);
      $asistente = $request->user()->asistente;

      $carrito = $this->buscarCarrito($asistente->id_usuario);

      if (!$carrito || $carrito->detalleCarritos->isEmpty()) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('No tienes entradas en el carrito');
        return $result->toResponse();
      }

      $compra = DB::transaction(function () use ($carrito, $asistente, $datos) {
        // Se bloquean las filas de los tipos de entrada implicados hasta el final
        // de la transacción, de modo que dos compras simultáneas no puedan vender
        // la misma entrada. Solo se toman los del evento que se está pagando
        $tipos = TipoEntrada::whereIn('id_tipo_entrada', $carrito->detalleCarritos->pluck('id_tipo_entrada'))
          ->where('id_evento', $datos['id_evento'])
          ->lockForUpdate()
          ->get()
          ->keyBy('id_tipo_entrada');

        // Las líneas del carrito que pertenecen a ese evento
        $lineas = [];
        foreach ($carrito->detalleCarritos as $detalle) {
          if ($tipos->has($detalle->id_tipo_entrada)) {
            $lineas[] = $detalle;
          }
        }

        if (empty($lineas)) {
          throw new \RuntimeException('No tienes entradas de ese evento en el carrito');
        }

        // El catálogo no ofrece eventos cancelados, pero pueden haberse quedado
        // en el carrito de quien los añadió antes de la cancelación
        $evento = Evento::find($datos['id_evento']);

        if ($evento->id_estado_evento === EstadoEvento::ESTADO_CANCELADO) {
          throw new \RuntimeException('Este evento ha sido cancelado');
        }

        $this->comprobarDisponibilidad($lineas, $tipos);
        $this->comprobarLimitePorEvento($lineas, $tipos, $asistente->id_usuario);

        // El importe se recalcula con los precios vigentes, no con los del carrito
        $bruto = $this->calcularBruto($lineas, $tipos);
        $puntos = $datos['puntos_canjeados'] ?? 0;
        $descuento = $this->calcularDescuento($puntos, $bruto, $asistente->puntos);

        $compra = Compra::create([
          'id_usuario' => $asistente->id_usuario,
          'id_estado_pago' => EstadoPago::ESTADO_PENDIENTE,
          'importe_total' => $bruto - $descuento,
          'puntos_ganados' => 0,
          'puntos_canjeados' => $puntos,
        ]);

        $this->generarDetalleCompra($compra, $lineas, $tipos);

        // Los puntos canjeados se descuentan al reservar, para que no puedan
        // gastarse dos veces en dos compras pendientes
        if ($puntos > 0) {
          $asistente->decrement('puntos', $puntos);
        }

        return $compra;
      });

      $compra->load('detalleCompras.tipoEntrada.evento:id_evento,nombre,fecha');

      $result->setStatusCode(ResultResponse::HTTP_CREATED);
      $result->setMessage(ResultResponse::MSG_CREATED);
      $result->setData($compra);
    } catch (\Illuminate\Validation\ValidationException $e) {
      $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
      $result->setMessage(ResultResponse::MSG_VALIDATION_ERROR);
      $result->setData($e->errors());
    } catch (\RuntimeException $e) {
      // Reglas de negocio incumplidas dentro de la transacción
      $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
      $result->setMessage($e->getMessage());
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Localiza el carrito del asistente. Cada uno tiene el suyo y es siempre el
  // mismo: las líneas entran al añadir entradas y salen al comprarlas
  private function buscarCarrito(string $idUsuario) {
    return Carrito::where('id_usuario', $idUsuario)
      ->with('detalleCarritos')
      ->first();
  }

  // Comprueba que queda aforo suficiente en cada tipo de entrada del carrito
  // Recibe las líneas del carrito que se van a comprar, no el carrito entero,
  // porque una compra abarca únicamente las entradas de un evento
  private function comprobarDisponibilidad(array $lineas, $tipos): void {
    foreach ($lineas as $detalle) {
      $tipo = $tipos[$detalle->id_tipo_entrada];

      if ($detalle->cantidad > ($tipo->aforo - $tipo->vendidas)) {
        throw new \RuntimeException("No quedan entradas suficientes de {$tipo->nombre}");
      }
    }
  }

  // Comprueba que el asistente no supera el máximo de entradas por evento,
  // sumando lo que ya tiene comprado y lo que lleva en el carrito
  private function comprobarLimitePorEvento(array $lineas, $tipos, string $idUsuario): void {
    $maximo = config('butaka.max_entradas_por_evento');
    $porEvento = [];

    // Se agrupan las líneas por evento, sumando sus cantidades
    foreach ($lineas as $detalle) {
      $idEvento = $tipos[$detalle->id_tipo_entrada]->id_evento;

      if (isset($porEvento[$idEvento])) {
        $acumulado = $porEvento[$idEvento];
      } else {
        $acumulado = 0;
      }

      $porEvento[$idEvento] = $acumulado + $detalle->cantidad;
    }

    foreach ($porEvento as $idEvento => $cantidad) {
      $yaCompradas = DetalleCompra::join('compras', 'compras.id_compra', '=', 'detalle_compras.id_compra')
        ->join('tipo_entradas', 'tipo_entradas.id_tipo_entrada', '=', 'detalle_compras.id_tipo_entrada')
        ->where('compras.id_usuario', $idUsuario)
        ->where('tipo_entradas.id_evento', $idEvento)
        // Las compras reembolsadas no ocupan cupo, ya que se devolvió el dinero
        ->where('compras.id_estado_pago', '!=', EstadoPago::ESTADO_REEMBOLSADO)
        ->sum('detalle_compras.cantidad');

      if (($yaCompradas + $cantidad) > $maximo) {
        // El mensaje detalla lo que ya se tiene, porque el límite cuenta las
        // compras anteriores y de otro modo resultaría incomprensible
        $restantes = $maximo - $yaCompradas;

        if ($restantes <= 0) {
          throw new \RuntimeException("Ya tienes el máximo de {$maximo} entradas de este evento");
        }

        throw new \RuntimeException("Ya tienes {$yaCompradas} entradas de este evento y el máximo por persona es {$maximo}, así que solo puedes adquirir {$restantes} más");
      }
    }
  }

  // Suma el importe del carrito con los precios vigentes, aplicando las ofertas
  private function calcularBruto(array $lineas, $tipos): float {
    $bruto = 0;

    foreach ($lineas as $detalle) {
      $tipo = $tipos[$detalle->id_tipo_entrada];
      $bruto += $this->precioEfectivo($tipo) * $detalle->cantidad;
    }

    return round($bruto, 2);
  }

  // Precio de venta de un tipo de entrada, ya descontada su oferta
  private function precioEfectivo(TipoEntrada $tipo): float {
    if (is_null($tipo->oferta)) {
      return (float) $tipo->precio;
    }

    return round($tipo->precio * (100 - $tipo->oferta) / 100, 2);
  }

  // Valida el canje y devuelve el descuento en euros que supone
  private function calcularDescuento(int $puntos, float $bruto, int $saldo): float {
    if ($puntos === 0) {
      return 0;
    }

    if ($puntos > $saldo) {
      throw new \RuntimeException('No dispone de puntos suficientes');
    }

    $minimo = config('butaka.min_puntos_canje');
    if ($puntos < $minimo) {
      throw new \RuntimeException("El canje mínimo es de {$minimo} puntos");
    }

    $descuento = round($puntos / config('butaka.puntos_por_euro_canje'), 2);
    $maximo = round($bruto * config('butaka.max_porcentaje_canje') / 100, 2);

    if ($descuento > $maximo) {
      throw new \RuntimeException('El canje no puede superar la mitad del importe de la compra');
    }

    return $descuento;
  }

  // Traslada las líneas del carrito a la compra y reserva el aforo
  private function generarDetalleCompra(Compra $compra, array $lineas, $tipos): void {
    foreach ($lineas as $detalle) {
      $tipo = $tipos[$detalle->id_tipo_entrada];

      DetalleCompra::create([
        'id_compra' => $compra->id_compra,
        'id_tipo_entrada' => $tipo->id_tipo_entrada,
        'cantidad' => $detalle->cantidad,
        'precio_unitario' => $this->precioEfectivo($tipo),
      ]);

      $tipo->increment('vendidas', $detalle->cantidad);

      // La línea desaparece del carrito, ya está comprada. Las de otros eventos
      // permanecen para pagarse por separado. El borrado se hace con las dos
      // claves porque la tabla tiene clave compuesta y el modelo no define una
      DetalleCarrito::where('id_carrito', $detalle->id_carrito)
        ->where('id_tipo_entrada', $detalle->id_tipo_entrada)
        ->delete();
    }
  }

  private function validarCompra(Request $request) {
    return $request->validate([
      // Cada compra corresponde a un único evento, así que el carrito se paga
      // por eventos y no de una vez
      'id_evento' => 'required|exists:eventos,id_evento',
      'puntos_canjeados' => 'nullable|integer|min:0',
    ]);
  }
}
