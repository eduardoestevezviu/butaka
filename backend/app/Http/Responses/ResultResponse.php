<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ResultResponse {

  /*
   * CÓDIGOS HTTP
   */
  const HTTP_OK = 200;
  const HTTP_CREATED = 201;
  const HTTP_NO_CONTENT = 204;
  const HTTP_UNPROCESSABLE = 422;
  const HTTP_UNAUTHORIZED = 401;
  const HTTP_FORBIDDEN = 403;
  const HTTP_NOT_FOUND = 404;
  const HTTP_CONFLICT = 409;
  const HTTP_ERROR = 500;

  /*
   * MENSAJES
   */
  const MSG_OK = 'OK';
  const MSG_CREATED = 'Creado exitosamente';
  const MSG_DELETED = 'Eliminado exitosamente';
  const MSG_UPDATED = 'Actualizado exitosamente';
  const MSG_NOT_FOUND = 'Elemento no encontrado';
  const MSG_ERROR = 'Error interno del servidor';
  const MSG_UNAUTHORIZED = 'Credenciales inválidas';
  const MSG_FORBIDDEN = 'No tiene permiso sobre este recurso';
  const MSG_CONFLICT = 'El recurso ya existe';
  const MSG_VALIDATION_ERROR = 'Error de validación';

  /*
   * PROPIEDADES
   */
  private int $statusCode;
  private string $message;
  private mixed $data;

  public function __construct() {
    $this->statusCode = self::HTTP_ERROR;
    $this->message = self::MSG_ERROR;
    $this->data = null;
  }

  /*
   * GETTERS Y SETTERS
   */
  public function getStatusCode(): int {
    return $this->statusCode;
  }

  public function setStatusCode(int $statusCode): void {
    $this->statusCode = $statusCode;
  }

  public function getMessage(): string {
    return $this->message;
  }

  public function setMessage(string $message): void {
    $this->message = $message;
  }

  public function getData(): mixed {
    return $this->data;
  }

  public function setData(mixed $data): void {
    $this->data = $data;
  }

  /*
   * MÉTODO DE RESPUESTA
   */
  public function toResponse(): JsonResponse {
    $response = [
      'statusCode' => $this->statusCode,
      'message' => $this->message,
    ];

    if (!is_null($this->data)) {
      $response['data'] = $this->data;
    }

    return response()->json($response, $this->statusCode);
  }
}
