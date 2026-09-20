<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\Usuario;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller {

  // Rol con el que nace toda cuenta, y único scope del token hasta que el
  // usuario aporte sus datos fiscales y pase a ser también organizador
  const ROL_POR_DEFECTO = 'asistente';

  // El alta la resuelve el UsuarioController, aquí solo se añade la emisión del
  // token para que el usuario quede identificado sin tener que iniciar sesión
  public function register(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $respuesta = app(UsuarioController::class)->store($request);

      // Si el alta falla se devuelve su respuesta tal cual, que ya
      // trae el código y el mensaje correctos
      if ($respuesta->getStatusCode() !== ResultResponse::HTTP_CREATED) {
        return $respuesta;
      }

      $usuario = Usuario::where('email', $request->email)->first();
      $token = $this->emitirToken($usuario);

      // El correo va en su propio try, porque un fallo del servidor de correo no
      // debe tumbar un alta que ya está hecha. El usuario existe y tiene su token
      try {
        Mail::to($usuario->email)->send(new WelcomeMail($usuario));
      } catch (\Exception $e) {
        // El registro es válido aunque la bienvenida no salga
      }

      $result->setStatusCode(ResultResponse::HTTP_CREATED);
      $result->setMessage('Usuario registrado exitosamente');
      $result->setData([
        'usuario' => $usuario->load('asistente'),
        'token' => $token,
        'token_type' => 'Bearer',
      ]);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function login(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $credenciales = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
      ]);

      // Se busca también entre las cuentas dadas de baja, porque iniciar sesión
      // es la vía para recuperarlas. Auth::attempt no sirve aquí, ya que el
      // borrado lógico las esconde de la consulta y devolvería credenciales
      // inválidas a un usuario que sí existe
      $usuario = Usuario::withTrashed()->where('email', $credenciales['email'])->first();

      // El mismo mensaje para correo inexistente y contraseña incorrecta, para no
      // revelar a un desconocido qué cuentas están dadas de alta
      if ($usuario === null || !Hash::check($credenciales['password'], $usuario->password)) {
        $result->setStatusCode(ResultResponse::HTTP_UNAUTHORIZED);
        $result->setMessage(ResultResponse::MSG_UNAUTHORIZED);
        return $result->toResponse();
      }

      // La reactivación ocurre después de comprobar la contraseña, que es lo
      // único que prueba que quien entra es el dueño de la cuenta
      $reactivada = false;
      if ($usuario->trashed()) {
        $usuario->restore();
        $reactivada = true;
      }

      $token = $this->emitirToken($usuario);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      if ($reactivada) {
        $result->setMessage('Cuenta reactivada, bienvenido de nuevo');
      } else {
        $result->setMessage('Inicio de sesión exitoso');
      }
      $result->setData([
        'usuario' => $usuario->load('asistente', 'organizador'),
        'token' => $token,
        'token_type' => 'Bearer',
      ]);
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

  // Revoca únicamente el token con el que se ha hecho la petición, de modo que
  // cerrar sesión en el móvil no cierra la del ordenador
  public function logout(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $request->user()->token()->revoke();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage('Cierre de sesión exitoso');
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // El token lleva como scopes todos los roles del usuario, porque en Butaka una
  // misma persona puede ser asistente y organizador a la vez. Emitir un único
  // scope obligaría a cerrar sesión para cambiar de faceta.
  // Es público porque el UsuarioController lo invoca al hacerse organizador
  public function emitirToken(Usuario $usuario): string {
    $roles = $usuario->getRoleNames()->toArray();

    if (empty($roles)) {
      $roles = [self::ROL_POR_DEFECTO];
    }

    // getRoleNames() carga la relación para leer los nombres, y si se deja cargada
    // viaja entera en la respuesta con su tabla intermedia. El frontend sabe si
    // alguien es organizador mirando si la relación organizador viene rellena
    $usuario->unsetRelation('roles');

    return $usuario->createToken('authToken', $roles)->accessToken;
  }
}
