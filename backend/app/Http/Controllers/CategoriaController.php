<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller {

  public function index(): JsonResponse {
    $result = new ResultResponse();
    try {
      // Las categorías son datos de referencia, no se paginan al ser un conjunto pequeño y fijo
      $categorias = Categoria::orderBy('id_categoria')->get();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($categorias);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }
}
