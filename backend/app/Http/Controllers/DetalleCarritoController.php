<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Models\TipoEntrada;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DetalleCarritoController extends Controller {

  // Devuelve el carrito activo del asistente autenticado con sus líneas
  public function carrito(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $carrito = $this->buscarCarrito($request->user()->id_usuario);

      if ($carrito) {
        $carrito->load([
          'detalleCarritos.tipoEntrada:id_tipo_entrada,nombre,precio,oferta,aforo,vendidas,id_evento',
          'detalleCarritos.tipoEntrada.evento:id_evento,nombre,fecha,imagen',
        ]);
      }

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($carrito);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function agregarEntrada(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $datos = $this->validarDetalleCarrito($request);
      $idUsuario = $request->user()->id_usuario;

      $tipoEntrada = TipoEntrada::findOrFail($datos['id_tipo_entrada']);

      // No tiene sentido reservar en el carrito más entradas de las que quedan
      if ($datos['cantidad'] > ($tipoEntrada->aforo - $tipoEntrada->vendidas)) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('No hay entradas suficientes disponibles');
        return $result->toResponse();
      }

      // Si el asistente no tiene carrito activo se le crea uno
      $carrito = $this->buscarCarrito($idUsuario) ?? Carrito::create(['id_usuario' => $idUsuario]);

      // Se realiza así por la clave compuesta, no se puede usar updateOrCreate
      $detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
        ->where('id_tipo_entrada', $datos['id_tipo_entrada'])
        ->first();

      if ($detalle) {
        DetalleCarrito::where('id_carrito', $carrito->id_carrito)
          ->where('id_tipo_entrada', $datos['id_tipo_entrada'])
          ->update([
            'cantidad' => $datos['cantidad'],
            'precio_unitario' => $tipoEntrada->precio,
          ]);
      } else {
        DetalleCarrito::create([
          'id_carrito' => $carrito->id_carrito,
          'id_tipo_entrada' => $datos['id_tipo_entrada'],
          'cantidad' => $datos['cantidad'],
          'precio_unitario' => $tipoEntrada->precio,
        ]);
      }

      $carrito->load('detalleCarritos.tipoEntrada.evento:id_evento,nombre,fecha,imagen');

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($carrito);
    } catch (\Illuminate\Validation\ValidationException $e) {
      $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
      $result->setMessage(ResultResponse::MSG_VALIDATION_ERROR);
      $result->setData($e->errors());
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function destroy(Request $request, TipoEntrada $tipoEntrada): JsonResponse {
    $result = new ResultResponse();
    try {
      $carrito = $this->buscarCarrito($request->user()->id_usuario);

      if (!$carrito) {
        $result->setStatusCode(ResultResponse::HTTP_NOT_FOUND);
        $result->setMessage(ResultResponse::MSG_NOT_FOUND);
        return $result->toResponse();
      }

      DetalleCarrito::where('id_carrito', $carrito->id_carrito)
        ->where('id_tipo_entrada', $tipoEntrada->id_tipo_entrada)
        ->delete();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_DELETED);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Localiza el carrito del asistente, que es único y permanente. Se vacía al
  // comprar o al quitar líneas, pero no desaparece
  private function buscarCarrito(string $idUsuario) {
    return Carrito::where('id_usuario', $idUsuario)->first();
  }

  private function validarDetalleCarrito(Request $request) {
    return $request->validate([
      'id_tipo_entrada' => 'required|exists:tipo_entradas,id_tipo_entrada',
      'cantidad' => 'required|integer|min:1',
    ]);
  }
}
