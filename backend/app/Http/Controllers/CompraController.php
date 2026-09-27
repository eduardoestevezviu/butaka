<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\EstadoCarrito;
use App\Models\EstadoPago;
use App\Models\TipoEntrada;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

  // Convierte el carrito activo del asistente en una compra pendiente de pago
  public function store(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $datos = $this->validarCompra($request);
      $asistente = $request->user()->asistente;

      $carrito = $this->buscarCarritoActivo($asistente->id_usuario);

      if (!$carrito || $carrito->detalleCarritos->isEmpty()) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('No hay ningún carrito activo con entradas');
        return $result->toResponse();
      }

      $compra = DB::transaction(function () use ($carrito, $asistente, $datos) {
        // Se bloquean las filas de los tipos de entrada implicados hasta el final
        // de la transacción, de modo que dos compras simultáneas no puedan vender
        // la misma entrada
        $tipos = TipoEntrada::whereIn('id_tipo_entrada', $carrito->detalleCarritos->pluck('id_tipo_entrada'))
          ->lockForUpdate()
          ->get()
          ->keyBy('id_tipo_entrada');

        $this->comprobarDisponibilidad($carrito, $tipos);
        $this->comprobarLimitePorEvento($carrito, $tipos, $asistente->id_usuario);

        // El importe se recalcula con los precios vigentes, no con los del carrito
        $bruto = $this->calcularBruto($carrito, $tipos);
        $puntos = $datos['puntos_canjeados'] ?? 0;
        $descuento = $this->calcularDescuento($puntos, $bruto, $asistente->puntos);

        $compra = Compra::create([
          'id_usuario' => $asistente->id_usuario,
          'id_estado_pago' => EstadoPago::ESTADO_PENDIENTE,
          'importe_total' => $bruto - $descuento,
          'puntos_ganados' => 0,
          'puntos_canjeados' => $puntos,
        ]);

        $this->generarDetalleCompra($compra, $carrito, $tipos);

        // Los puntos canjeados se descuentan al reservar, para que no puedan
        // gastarse dos veces en dos compras pendientes
        if ($puntos > 0) {
          $asistente->decrement('puntos', $puntos);
        }

        // El carrito queda convertido y no puede volver a usarse
        $carrito->update(['id_estado_carrito' => EstadoCarrito::ESTADO_CONVERTIDO]);

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

  private function buscarCarritoActivo(string $idUsuario) {
    return Carrito::where('id_usuario', $idUsuario)
      ->where('id_estado_carrito', EstadoCarrito::ESTADO_ACTIVO)
      ->with('detalleCarritos')
      ->first();
  }

  // Comprueba que queda aforo suficiente en cada tipo de entrada del carrito
  private function comprobarDisponibilidad(Carrito $carrito, $tipos): void {
    foreach ($carrito->detalleCarritos as $detalle) {
      $tipo = $tipos[$detalle->id_tipo_entrada];

      if ($detalle->cantidad > ($tipo->aforo - $tipo->vendidas)) {
        throw new \RuntimeException("No quedan entradas suficientes de {$tipo->nombre}");
      }
    }
  }

  // Comprueba que el asistente no supera el máximo de entradas por evento,
  // sumando lo que ya tiene comprado y lo que lleva en el carrito
  private function comprobarLimitePorEvento(Carrito $carrito, $tipos, string $idUsuario): void {
    $maximo = config('butaka.max_entradas_por_evento');
    $porEvento = [];

    // Se agrupan las líneas del carrito por evento, sumando sus cantidades
    foreach ($carrito->detalleCarritos as $detalle) {
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
  private function calcularBruto(Carrito $carrito, $tipos): float {
    $bruto = 0;

    foreach ($carrito->detalleCarritos as $detalle) {
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
  private function generarDetalleCompra(Compra $compra, Carrito $carrito, $tipos): void {
    foreach ($carrito->detalleCarritos as $detalle) {
      $tipo = $tipos[$detalle->id_tipo_entrada];

      DetalleCompra::create([
        'id_compra' => $compra->id_compra,
        'id_tipo_entrada' => $tipo->id_tipo_entrada,
        'cantidad' => $detalle->cantidad,
        'precio_unitario' => $this->precioEfectivo($tipo),
      ]);

      $tipo->increment('vendidas', $detalle->cantidad);
    }
  }

  private function validarCompra(Request $request) {
    return $request->validate([
      'puntos_canjeados' => 'nullable|integer|min:0',
    ]);
  }
}
