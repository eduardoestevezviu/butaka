<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\TipoEntrada;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TipoEntradaController extends Controller {

  // Añade un tipo de entrada a un evento ya existente
  public function store(Request $request, Evento $evento): JsonResponse {
    $result = new ResultResponse();
    try {
      // Un organizador solo puede tocar los tipos de entrada de sus propios eventos
      if ($evento->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      $datos = $this->validarTipoEntrada($request);
      $datos['id_evento'] = $evento->id_evento;

      $tipoEntrada = TipoEntrada::create($datos);

      $result->setStatusCode(ResultResponse::HTTP_CREATED);
      $result->setMessage(ResultResponse::MSG_CREATED);
      $result->setData($tipoEntrada);
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

  public function update(Request $request, TipoEntrada $tipoEntrada): JsonResponse {
    $result = new ResultResponse();
    try {
      if (!$this->esPropietario($request, $tipoEntrada)) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      $datos = $this->validarActualizacion($request);

      // El aforo nunca puede quedar por debajo de las entradas ya vendidas
      if (isset($datos['aforo']) && $datos['aforo'] < $tipoEntrada->vendidas) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('El aforo no puede ser inferior a las entradas ya vendidas');
        return $result->toResponse();
      }

      $tipoEntrada->update($datos);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_UPDATED);
      $result->setData($tipoEntrada);
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
      if (!$this->esPropietario($request, $tipoEntrada)) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      // Un tipo de entrada con ventas no se elimina, ya que de él cuelgan compras y facturas
      if ($tipoEntrada->vendidas > 0) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('El tipo de entrada tiene ventas registradas y no puede eliminarse');
        return $result->toResponse();
      }

      // Un evento no puede quedarse sin ningún tipo de entrada
      if ($this->esElUnicoTipo($tipoEntrada)) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('El evento debe conservar al menos un tipo de entrada');
        return $result->toResponse();
      }

      $tipoEntrada->delete();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_DELETED);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Comprueba que el tipo de entrada pertenece a un evento del organizador autenticado
  private function esPropietario(Request $request, TipoEntrada $tipoEntrada): bool {
    return $tipoEntrada->evento->id_usuario === $request->user()->id_usuario;
  }

  // Comprueba si es el último tipo de entrada que le queda al evento
  private function esElUnicoTipo(TipoEntrada $tipoEntrada): bool {
    return TipoEntrada::where('id_evento', $tipoEntrada->id_evento)->count() <= 1;
  }

  private function validarTipoEntrada(Request $request) {
    return $request->validate([
      'nombre' => 'required|string|max:100',
      'precio' => 'required|numeric|min:0',
      'aforo' => 'required|integer|min:1',
      'oferta' => 'nullable|integer|min:1|max:100',
    ]);
  }

  private function validarActualizacion(Request $request) {
    return $request->validate([
      'nombre' => 'sometimes|string|max:100',
      'precio' => 'sometimes|numeric|min:0',
      'aforo' => 'sometimes|integer|min:1',
      'oferta' => 'nullable|integer|min:1|max:100',
    ]);
  }
}
