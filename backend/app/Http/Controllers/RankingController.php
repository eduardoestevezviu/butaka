<?php

namespace App\Http\Controllers;

use App\Models\EstadoEvento;
use App\Models\Organizador;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RankingController extends Controller {

  // Posición del organizador autenticado en el ranking de Butaka. El ranking se
  // ordena por ocupación media
  public function posicionamiento(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $clasificacion = $this->calcularClasificacion();

      // Se busca al organizador que hace la petición dentro de la clasificación
      $posicion = null;
      $propia = null;
      foreach ($clasificacion as $indice => $fila) {
        if ($fila['id_usuario'] === $request->user()->id_usuario) {
          $posicion = $indice + 1;
          $propia = $fila;
          break;
        }
      }

      // Solo se devuelve su propia posición
      $result->setStatusCode(ResultResponse::HTTP_OK);

      if ($posicion === null) {
        $result->setMessage('Todavía no tiene eventos con los que entrar en el ranking');
        $result->setData([
          'posicion' => null,
          'total_organizadores' => count($clasificacion),
        ]);
      } else {
        $result->setMessage(ResultResponse::MSG_OK);
        $result->setData([
          'posicion' => $posicion,
          'total_organizadores' => count($clasificacion),
          'ocupacion_media' => round($propia['ocupacion'], 1),
          'eventos_computados' => $propia['eventos'],
          'entradas_vendidas' => $propia['vendidas'],
        ]);
      }
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Calcula la ocupación media de cada organizador y los ordena de mayor a menor.
  // Es pública porque el catálogo la usa para destacar a los mejor posicionados
  public function calcularClasificacion(): array {
    $organizadores = Organizador::with([
      'eventos' => function ($consulta) {
        $consulta->where('id_estado_evento', '!=', EstadoEvento::ESTADO_CANCELADO);
      },
      'eventos.tipoEntradas',
    ])->get();

    $clasificacion = [];

    foreach ($organizadores as $organizador) {
      $porcentajes = [];
      $vendidasTotales = 0;

      foreach ($organizador->eventos as $evento) {
        $aforo = 0;
        $vendidas = 0;
        foreach ($evento->tipoEntradas as $tipo) {
          $aforo = $aforo + $tipo->aforo;
          $vendidas = $vendidas + $tipo->vendidas;
        }

        if ($aforo > 0) {
          $porcentajes[] = $vendidas / $aforo * 100;
        }
        $vendidasTotales = $vendidasTotales + $vendidas;
      }

      // Un organizador sin eventos no tiene ocupación que medir
      if (empty($porcentajes)) {
        continue;
      }

      $clasificacion[] = [
        'id_usuario' => $organizador->id_usuario,
        'ocupacion' => array_sum($porcentajes) / count($porcentajes),
        'eventos' => count($porcentajes),
        'vendidas' => $vendidasTotales,
      ];
    }

    // Mayor ocupación primero. A igualdad de ocupación, gana quien más ha vendido
    usort($clasificacion, function ($a, $b) {
      if ($a['ocupacion'] == $b['ocupacion']) {
        return $b['vendidas'] <=> $a['vendidas'];
      }
      return $b['ocupacion'] <=> $a['ocupacion'];
    });

    return $clasificacion;
  }
}
