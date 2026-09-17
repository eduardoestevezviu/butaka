<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\EstadoEvento;
use App\Models\TipoEntrada;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller {

  public function index(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      // El catálogo público solo muestra eventos publicados que aún no se han celebrado
      $query = Evento::where('id_estado_evento', EstadoEvento::ESTADO_PUBLICADO)
        ->where('fecha', '>=', now());

      // Filtra los eventos por categoría
      $query = $this->filtrarPorCategoria($request, $query);

      // Filtra los eventos por búsqueda libre de texto
      $query = $this->busquedaLibre($request, $query)->orderBy('fecha', 'asc');

      // Se cargan por adelantado las relaciones que necesita la tarjeta del catálogo
      $query->with([
        'organizador:id_usuario,nombre_comercial',
        'categoria:id_categoria,categoria',
        'tipoEntradas:id_tipo_entrada,nombre,precio,oferta,aforo,vendidas,id_evento',
      ]);

      $eventos = $query->paginate(config('butaka.eventos_por_pagina'));

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($eventos);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function show(Evento $evento): JsonResponse {
    $result = new ResultResponse();
    try {
      $evento->load([
        'organizador:id_usuario,nombre_comercial',
        'categoria:id_categoria,categoria',
        'estadoEvento:id_estado_evento,estado_evento',
        'tipoEntradas',
      ]);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($evento);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Listado de eventos del organizador autenticado, para su panel
  public function misEventos(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $eventos = Evento::where('id_usuario', $request->user()->id_usuario)
        ->with([
          'categoria:id_categoria,categoria',
          'estadoEvento:id_estado_evento,estado_evento',
          'tipoEntradas:id_tipo_entrada,nombre,precio,aforo,vendidas,id_evento',
        ])
        ->orderBy('fecha', 'desc')
        ->paginate(config('butaka.eventos_por_pagina'));

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($eventos);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function store(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      // Validar los datos de entrada, incluidos los tipos de entrada
      $datos = $this->validarEvento($request);

      // El evento y sus tipos de entrada se crean juntos, ya que un evento
      // sin ningún tipo de entrada no se puede vender
      $evento = DB::transaction(function () use ($datos, $request) {
        $nuevoEvento = [
          'nombre' => $datos['nombre'],
          'descripcion' => $datos['descripcion'] ?? null,
          'ubicacion' => $datos['ubicacion'],
          'fecha' => $datos['fecha'],
          'id_categoria' => $datos['id_categoria'],
          'id_usuario' => $request->user()->id_usuario,
        ];

        // La imagen solo se envía si el organizador la aporta, para que la base
        // de datos aplique la imagen por defecto cuando no venga
        if (!empty($datos['imagen'])) {
          $nuevoEvento['imagen'] = $datos['imagen'];
        }

        $evento = Evento::create($nuevoEvento);

        $this->crearTipoEntradas($evento, $datos['tipo_entradas']);

        return $evento;
      });

      $evento->load('categoria:id_categoria,categoria', 'tipoEntradas');

      $result->setStatusCode(ResultResponse::HTTP_CREATED);
      $result->setMessage(ResultResponse::MSG_CREATED);
      $result->setData($evento);
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

  public function update(Request $request, Evento $evento): JsonResponse {
    $result = new ResultResponse();
    try {
      // Un organizador solo puede modificar sus propios eventos
      if (!$this->esPropietario($request, $evento)) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      $datos = $this->validarActualizacion($request);
      $evento->update($datos);

      $evento->load('categoria:id_categoria,categoria', 'tipoEntradas');

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_UPDATED);
      $result->setData($evento);
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

  public function destroy(Request $request, Evento $evento): JsonResponse {
    $result = new ResultResponse();
    try {
      if (!$this->esPropietario($request, $evento)) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      // Un evento con entradas vendidas no se elimina, se cancela, para no dejar
      // sin respaldo las compras y las facturas ya emitidas
      if ($this->tieneVentas($evento)) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('El evento tiene entradas vendidas, debe cancelarse en lugar de eliminarse');
        return $result->toResponse();
      }

      $evento->delete();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_DELETED);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Filtra el catálogo por la categoría recibida en la petición
  private function filtrarPorCategoria(Request $request, $query) {
    if ($request->has('categoria')) {
      $query->where('id_categoria', $request->categoria);
    }

    return $query;
  }

  // Búsqueda libre sobre el nombre, la ubicacion y la descripcion del evento
  private function busquedaLibre(Request $request, $query) {
    if ($request->has('buscar')) {
      $texto = $request->buscar;
      $query->where(function ($q) use ($texto) {
        $q->where('nombre', 'like', "%{$texto}%")
          ->orWhere('ubicacion', 'like', "%{$texto}%")
          ->orWhere('descripcion', 'like', "%{$texto}%");
      });
    }

    return $query;
  }

  // Comprueba que el evento pertenece al organizador que realiza la petición
  private function esPropietario(Request $request, Evento $evento): bool {
    return $evento->id_usuario === $request->user()->id_usuario;
  }

  // Un evento tiene ventas si alguno de sus tipos de entrada registra entradas vendidas
  private function tieneVentas(Evento $evento): bool {
    return $evento->tipoEntradas()->where('vendidas', '>', 0)->exists();
  }

  // Crea los tipos de entrada asociados a un evento
  private function crearTipoEntradas(Evento $evento, array $tipoEntradas): void {
    foreach ($tipoEntradas as $tipo) {
      TipoEntrada::create([
        'nombre' => $tipo['nombre'],
        'precio' => $tipo['precio'],
        'oferta' => $tipo['oferta'] ?? null,
        'aforo' => $tipo['aforo'],
        'id_evento' => $evento->id_evento,
      ]);
    }
  }

  private function validarEvento(Request $request) {
    return $request->validate([
      'nombre' => 'required|string|max:150',
      'descripcion' => 'nullable|string',
      'ubicacion' => 'required|string|max:255',
      'fecha' => 'required|date|after:now',
      'imagen' => 'nullable|string|max:255',
      'id_categoria' => 'required|exists:categorias,id_categoria',
      'tipo_entradas' => 'required|array|min:1',
      'tipo_entradas.*.nombre' => 'required|string|max:100',
      'tipo_entradas.*.precio' => 'required|numeric|min:0',
      'tipo_entradas.*.aforo' => 'required|integer|min:1',
      'tipo_entradas.*.oferta' => 'nullable|integer|min:1|max:100',
    ]);
  }

  private function validarActualizacion(Request $request) {
    return $request->validate([
      'nombre' => 'sometimes|string|max:150',
      'descripcion' => 'nullable|string',
      'ubicacion' => 'sometimes|string|max:255',
      'fecha' => 'sometimes|date|after:now',
      'imagen' => 'sometimes|string|max:255',
      'id_categoria' => 'sometimes|exists:categorias,id_categoria',
    ]);
  }
}
