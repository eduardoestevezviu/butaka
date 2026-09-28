<?php

namespace App\Http\Controllers;

use App\Mail\CompraMail;
use App\Models\Compra;
use App\Models\EstadoEvento;
use App\Models\EstadoPago;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\Stripe;

class PagoController extends Controller {

  // Abre una sesión de pago en Stripe para una compra pendiente y devuelve la
  // dirección de la pasarela, a la que el frontend redirige al asistente
  public function store(Request $request, Compra $compra): JsonResponse {
    $result = new ResultResponse();
    try {
      // Nadie puede pagar la compra de otro
      if ($compra->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      if ($compra->id_estado_pago !== EstadoPago::ESTADO_PENDIENTE) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('Esta compra no está pendiente de pago');
        return $result->toResponse();
      }

      // Cobrar por un evento cancelado obligaría a devolver el dinero acto
      // seguido, así que el pago se rechaza
      if ($this->esDeEventoCancelado($compra)) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('El evento de esta compra ha sido cancelado');
        return $result->toResponse();
      }

      Stripe::setApiKey(config('services.stripe.secret'));

      $frontend = config('butaka.frontend_url');

      $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
          [
            'price_data' => [
              'currency' => 'eur',
              'product_data' => [
                'name' => 'Entradas Butaka',
              ],
              // Stripe trabaja en céntimos, y con enteros para no arrastrar
              // errores de coma flotante
              'unit_amount' => intval(round($compra->importe_total * 100)),
            ],
            'quantity' => 1,
          ]
        ],
        'mode' => 'payment',
        'success_url' => $frontend . '/compras?session_id={CHECKOUT_SESSION_ID}&id_compra=' . $compra->id_compra,
        'cancel_url' => $frontend . '/compras?pago=cancelado',
      ]);

      // El identificador de la sesión queda guardado para poder verificar el
      // pago más tarde y, llegado el caso, emitir el reembolso
      $compra->session_id = $session->id;
      $compra->save();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData(['url' => $session->url]);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Indica si alguna de las entradas de la compra pertenece a un evento cancelado
  private function esDeEventoCancelado(Compra $compra): bool {
    $compra->load('detalleCompras.tipoEntrada.evento:id_evento,id_estado_evento');

    foreach ($compra->detalleCompras as $detalle) {
      $evento = $detalle->tipoEntrada->evento;

      if ($evento->id_estado_evento === EstadoEvento::ESTADO_CANCELADO) {
        return true;
      }
    }

    return false;
  }

  // Devuelve por Stripe el importe de una compra ya pagada. Lo usa la cancelación
  // de eventos, y se deja aquí porque es este controlador el que trata con la
  // pasarela. Lanza una excepción si el reembolso no se puede emitir
  public function reembolsar(Compra $compra): void {
    if ($compra->session_id === null) {
      throw new \RuntimeException('La compra no tiene ningún pago que devolver');
    }

    Stripe::setApiKey(config('services.stripe.secret'));

    // El cobro no se identifica con la sesión sino con la intención de pago que
    // esta generó, que es lo que Stripe sabe reembolsar
    $session = Session::retrieve($compra->session_id);

    if ($session->payment_intent === null) {
      throw new \RuntimeException('La compra no llegó a cobrarse');
    }

    Refund::create([
      'payment_intent' => $session->payment_intent,
    ]);
  }

  // Comprueba en Stripe si la compra está pagada y, en ese caso, la completa:
  // concede los puntos de fidelización y emite la factura
  public function verificar(Request $request, Compra $compra): JsonResponse {
    $result = new ResultResponse();
    try {
      if ($compra->id_usuario !== $request->user()->id_usuario) {
        $result->setStatusCode(ResultResponse::HTTP_FORBIDDEN);
        $result->setMessage(ResultResponse::MSG_FORBIDDEN);
        return $result->toResponse();
      }

      // Si ya estaba completada se responde que todo está bien, porque el
      // asistente puede recargar la pantalla de vuelta de la pasarela
      if ($compra->id_estado_pago === EstadoPago::ESTADO_COMPLETADO) {
        $result->setStatusCode(ResultResponse::HTTP_OK);
        $result->setMessage('El pago ya estaba confirmado');
        $result->setData($compra->load('factura'));
        return $result->toResponse();
      }

      if ($compra->session_id === null) {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('Esta compra no tiene ningún pago iniciado');
        return $result->toResponse();
      }

      Stripe::setApiKey(config('services.stripe.secret'));

      // La consulta a Stripe se hace antes de abrir la transacción, para no
      // mantener bloqueada la base de datos mientras dura una llamada de red
      $session = Session::retrieve($compra->session_id);

      if ($session->payment_status !== 'paid') {
        $result->setStatusCode(ResultResponse::HTTP_UNPROCESSABLE);
        $result->setMessage('El pago no se ha completado');
        return $result->toResponse();
      }

      DB::transaction(function () use ($compra) {
        // Un punto por cada euro abonado, sin decimales
        $puntos = (int) floor($compra->importe_total * config('butaka.puntos_por_euro'));

        $compra->update([
          'id_estado_pago' => EstadoPago::ESTADO_COMPLETADO,
          'fecha_pago' => now(),
          'puntos_ganados' => $puntos,
        ]);

        if ($puntos > 0) {
          $compra->asistente->increment('puntos', $puntos);
        }

        app(FacturaController::class)->emitirDeCompra($compra);
      });

      // El aviso se envía solo aquí, en el paso de pendiente a pagada. Si el asistente
      // recarga la pantalla de vuelta de la pasarela, la respuesta sale antes por la
      // rama idempotente y no se reenvía.
      // Va fuera de la transacción porque enviar es una llamada de red, y en su propio
      // try porque un fallo del servidor de correo no puede deshacer un pago ya cobrado
      try {
        $compra->load('detalleCompras.tipoEntrada.evento');
        $evento = $compra->detalleCompras->first()->tipoEntrada->evento;

        Mail::to($request->user()->email)->send(new CompraMail($request->user(), $compra, $evento));
      } catch (\Exception $e) {
        // La compra está pagada igualmente, el correo es solo un aviso
      }

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage('Pago confirmado correctamente');
      $result->setData($compra->fresh()->load('factura'));
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }
}
