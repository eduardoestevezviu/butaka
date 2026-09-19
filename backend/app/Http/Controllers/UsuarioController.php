<?php

namespace App\Http\Controllers;

use App\Models\Asistente;
use App\Models\Organizador;
use App\Models\Usuario;
use App\Http\Responses\ResultResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller {

  // Alta de un usuario nuevo, invocado desde el registro del AuthController
  public function store(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $datos = $this->validarUsuario($request);

      // Se comprueban también las cuentas dadas de baja, ya que conservan su correo.
      // Quien quiera recuperar la suya debe hacerlo iniciando sesión
      if ($this->existeCorreo($datos['email'])) {
        $result->setStatusCode(ResultResponse::HTTP_CONFLICT);
        $result->setMessage('El correo ya está registrado, inicie sesión');
        return $result->toResponse();
      }

      $datos['password'] = Hash::make($datos['password']);

      // Todo usuario nace como asistente, por lo que ambas filas se crean juntas.
      // El rol entra en la misma transacción para que nadie quede registrado sin
      // rol si la asignación falla, lo que le dejaría un token sin permisos
      $usuario = DB::transaction(function () use ($datos) {
        $usuario = Usuario::create($datos);

        Asistente::create(['id_usuario' => $usuario->id_usuario]);

        // El guard toma el que hay por defecto en config/auth.php, que es api
        $usuario->assignRole('asistente');

        return $usuario;
      });

      $result->setStatusCode(ResultResponse::HTTP_CREATED);
      $result->setMessage(ResultResponse::MSG_CREATED);
      $result->setData($usuario);
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

  // Perfil del usuario autenticado
  public function show(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $usuario = $request->user()->load('asistente', 'organizador');

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_OK);
      $result->setData($usuario);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  public function update(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $usuario = $request->user();
      $datos = $this->validarActualizacion($request, $usuario);

      // La contraseña solo se cifra y actualiza si el usuario la ha rellenado
      if (!empty($datos['password'])) {
        $datos['password'] = Hash::make($datos['password']);
      } else {
        unset($datos['password']);
      }

      $usuario->update($datos);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_UPDATED);
      $result->setData($usuario->load('asistente', 'organizador'));
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

  // Baja de la cuenta, reversible iniciando sesión de nuevo
  public function destroy(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $request->user()->delete();

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_DELETED);
    } catch (\Exception $e) {
      $result->setStatusCode(ResultResponse::HTTP_ERROR);
      $result->setMessage(ResultResponse::MSG_ERROR);
    }

    return $result->toResponse();
  }

  // Un asistente pasa a ser también organizador aportando sus datos fiscales
  public function createOrganizador(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $usuario = $request->user();

      if ($usuario->organizador) {
        $result->setStatusCode(ResultResponse::HTTP_CONFLICT);
        $result->setMessage('El usuario ya figura como organizador');
        return $result->toResponse();
      }

      $datos = $this->validarOrganizador($request);
      $datos['id_usuario'] = $usuario->id_usuario;

      // La fila de organizador y el rol nacen juntos, igual que en el alta
      DB::transaction(function () use ($datos, $usuario) {
        Organizador::create($datos);

        $usuario->assignRole('organizador');
      });

      // Los scopes se congelan al emitir el token, así que todos los que estén
      // en circulación siguen sin el de organizador y darían un 403 al publicar
      // un evento. Se revocan todos y se entrega uno nuevo con ambos scopes, que
      // es el mismo criterio que aplica cualquier web al cambiar la contraseña.
      // Revocar antes de crear, para no anular el que se acaba de emitir
      $usuario->tokens()->update(['revoked' => true]);

      $usuario->refresh();
      $token = app(AuthController::class)->emitirToken($usuario);

      $result->setStatusCode(ResultResponse::HTTP_CREATED);
      $result->setMessage('Ya puede publicar eventos. Por seguridad se han cerrado sus otras sesiones');
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

  // Modificación de los datos fiscales y del nombre público del organizador
  public function updateOrganizador(Request $request): JsonResponse {
    $result = new ResultResponse();
    try {
      $usuario = $request->user();
      $organizador = $usuario->organizador;

      if (!$organizador) {
        $result->setStatusCode(ResultResponse::HTTP_NOT_FOUND);
        $result->setMessage('El usuario no figura como organizador');
        return $result->toResponse();
      }

      $datos = $this->validarActualizacionOrganizador($request, $organizador);
      $organizador->update($datos);

      $result->setStatusCode(ResultResponse::HTTP_OK);
      $result->setMessage(ResultResponse::MSG_UPDATED);
      $result->setData($usuario->load('asistente', 'organizador'));
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

  // Comprueba si el correo está en uso, incluidas las cuentas dadas de baja
  private function existeCorreo(string $email): bool {
    return Usuario::withTrashed()->where('email', $email)->exists();
  }

  private function validarUsuario(Request $request) {
    return $request->validate([
      'nombre' => 'required|string|max:100',
      'apellidos' => 'required|string|max:100',
      'email' => 'required|email|max:100',
      'password' => 'required|string|min:8',
      'numero_telefono' => 'nullable|string|max:20',
      'calle' => 'nullable|string|max:255',
      'vivienda' => 'nullable|string|max:50',
      'ciudad' => 'nullable|string|max:100',
      'codigo_postal' => 'nullable|string|max:5',
    ]);
  }

  private function validarActualizacion(Request $request, Usuario $usuario) {
    return $request->validate([
      'nombre' => 'sometimes|string|max:100',
      'apellidos' => 'sometimes|string|max:100',
      'password' => 'nullable|string|min:8',
      'numero_telefono' => 'nullable|string|max:20',
      'calle' => 'nullable|string|max:255',
      'vivienda' => 'nullable|string|max:50',
      'ciudad' => 'nullable|string|max:100',
      'codigo_postal' => 'nullable|string|max:5',
    ]);
  }

  private function validarOrganizador(Request $request) {
    return $request->validate([
      'nombre_comercial' => 'required|string|max:150|unique:organizadores,nombre_comercial',
      'nif' => 'required|string|size:9|unique:organizadores,nif',
      'iban' => 'required|string|max:34',
    ]);
  }

  // Se excluye la propia fila de la comprobación de unicidad, para que el
  // organizador pueda guardar sin cambiar el nombre comercial o el NIF
  private function validarActualizacionOrganizador(Request $request, $organizador) {
    return $request->validate([
      'nombre_comercial' => [
        'sometimes', 'string', 'max:150',
        Rule::unique('organizadores', 'nombre_comercial')->ignore($organizador->id_usuario, 'id_usuario'),
      ],
      'nif' => [
        'sometimes', 'string', 'size:9',
        Rule::unique('organizadores', 'nif')->ignore($organizador->id_usuario, 'id_usuario'),
      ],
      'iban' => 'sometimes|string|max:34',
    ]);
  }
}
