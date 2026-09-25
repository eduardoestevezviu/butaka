<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ApiForceAcceptHeader;
use App\Http\Responses\ResultResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
      $middleware->append(ApiForceAcceptHeader::class);

      // Limite general de la API: 120 peticiones por minuto. El limitador cuenta
      // por usuario cuando hay sesion iniciada, y por direccion IP cuando no
      $middleware->api(append: ['throttle:120,1']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
      // Un recurso inexistente localizado por route model binding devuelve 404 en JSON
      $exceptions->render(function (ModelNotFoundException $e) {
        return response()->json([
          'statusCode' => ResultResponse::HTTP_NOT_FOUND,
          'message' => ResultResponse::MSG_NOT_FOUND
        ], ResultResponse::HTTP_NOT_FOUND);
      });

      // Una ruta que no existe devuelve tambien 404 en JSON en lugar de una pagina HTML
      $exceptions->render(function (NotFoundHttpException $e) {
        return response()->json([
          'statusCode' => ResultResponse::HTTP_NOT_FOUND,
          'message' => ResultResponse::MSG_NOT_FOUND
        ], ResultResponse::HTTP_NOT_FOUND);
      });

      // Quien supera el limite de peticiones recibe un 429 con el mismo formato que
      // el resto de la API
      $exceptions->render(function (ThrottleRequestsException $e) {
        return response()->json([
          'statusCode' => ResultResponse::HTTP_TOO_MANY_REQUESTS,
          'message' => ResultResponse::MSG_TOO_MANY_REQUESTS
        ], ResultResponse::HTTP_TOO_MANY_REQUESTS, $e->getHeaders());
      });
    })->create();
