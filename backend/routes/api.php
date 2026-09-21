<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DetalleCarritoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\TipoEntradaController;
use App\Http\Controllers\UsuarioController;

use Laravel\Passport\Http\Middleware\CheckToken;


/*********************
 * Rutas de la API
 *********************/

/*************************************************************************************
 *                            RUTAS PARA ORGANIZADORES
 *************************************************************************************/
// Exigen el scope de organizador, que solo lleva el token de quien ha aportado
// sus datos fiscales. La comprobación de propiedad de cada recurso se hace
// dentro del controlador, ya que el scope dice qué es el usuario, no qué es suyo
Route::middleware(['auth:api', CheckToken::using('organizador')])->group(function () {

  /**
   * RUTAS EVENTOS
   */
  Route::controller(EventoController::class)->prefix('eventos')->group(function () {

    // POST /api/eventos - Crear evento junto con sus tipos de entrada
    Route::post('/', 'store');

    // PUT /api/eventos/{id} - Actualizar los datos generales de un evento
    Route::put('/{evento}', 'update');

    // DELETE /api/eventos/{id} - Eliminar un evento sin ventas
    Route::delete('/{evento}', 'destroy');
  });

  // GET /api/organizador/eventos - Listar los eventos del organizador para su panel
  Route::get('/organizador/eventos', [EventoController::class, 'misEventos']);

  // GET /api/organizador/eventos/{id}/ventas - Entradas vendidas y recaudación de un evento
  Route::get('/organizador/eventos/{evento}/ventas', [EventoController::class, 'ventas']);

  // GET /api/organizador/facturacion - Liquidaciones del organizador, filtrables con ?evento={id}
  Route::get('/organizador/facturacion', [FacturaController::class, 'facturacion']);


  /**
   * RUTAS TIPOS DE ENTRADA
   */
  // POST /api/eventos/{id}/tipo-entradas - Añadir una modalidad a un evento existente
  Route::post('/eventos/{evento}/tipo-entradas', [TipoEntradaController::class, 'store']);

  Route::controller(TipoEntradaController::class)->prefix('tipo-entradas')->group(function () {

    // PUT /api/tipo-entradas/{id} - Actualizar precio, oferta o aforo de una modalidad
    Route::put('/{tipoEntrada}', 'update');

    // DELETE /api/tipo-entradas/{id} - Eliminar una modalidad sin ventas
    Route::delete('/{tipoEntrada}', 'destroy');
  });
});


/*************************************************************************************
 *                            RUTAS PARA ASISTENTES
 *************************************************************************************/
// Todo usuario registrado es asistente, de modo que este grupo cubre también a los
// organizadores cuando actúan como compradores
Route::middleware(['auth:api', CheckToken::using('asistente')])->group(function () {

  /**
   * RUTAS PERFIL
   */
  // El usuario sale del token, por lo que ninguna de estas rutas lleva identificador
  Route::controller(UsuarioController::class)->prefix('perfil')->group(function () {

    // GET /api/perfil - Datos del usuario autenticado
    Route::get('/', 'show');

    // PUT /api/perfil - Actualizar los datos personales
    Route::put('/', 'update');

    // DELETE /api/perfil - Baja de la cuenta, reversible iniciando sesión
    Route::delete('/', 'destroy');

    // POST /api/perfil/organizador - Aportar los datos fiscales y pasar a ser organizador
    Route::post('/organizador', 'createOrganizador');

    // PUT /api/perfil/organizador - Actualizar nombre comercial, NIF o IBAN
    Route::put('/organizador', 'updateOrganizador');
  });


  /**
   * RUTAS CARRITO
   */
  Route::controller(DetalleCarritoController::class)->prefix('carrito')->group(function () {

    // GET /api/carrito - Carrito activo del asistente con sus líneas
    Route::get('/', 'carrito');

    // POST /api/carrito - Añadir o actualizar una línea del carrito
    Route::post('/', 'agregarEntrada');

    // DELETE /api/carrito/{id} - Quitar del carrito una modalidad concreta
    Route::delete('/{tipoEntrada}', 'destroy');
  });


  /**
   * RUTAS COMPRAS
   */
  Route::controller(CompraController::class)->prefix('compras')->group(function () {

    // GET /api/compras - Listado de compras del asistente
    Route::get('/', 'index');

    // GET /api/compras/{id} - Detalle de una compra con su factura
    Route::get('/{compra}', 'show');

    // POST /api/compras - Convertir el carrito activo en una compra pendiente de pago
    Route::post('/', 'store');
  });


  /**
   * RUTAS FACTURAS
   */
  Route::controller(FacturaController::class)->prefix('facturas')->group(function () {

    // GET /api/facturas - Comprobantes de compra y liquidaciones del usuario
    Route::get('/', 'index');

    // GET /api/facturas/{id} - Detalle de una factura con su desglose
    Route::get('/{id}', 'show');
  });


  /**
   * RUTAS SESIÓN
   */
  // POST /api/logout - Revocar el token con el que se hace la petición
  Route::post('/logout', [AuthController::class, 'logout']);
});


/*************************************************************************************
 *                            RUTAS PÚBLICAS PARA TODOS LOS USUARIOS
 *************************************************************************************/

/**
 * RUTAS EVENTOS
 */
Route::controller(EventoController::class)->prefix('eventos')->group(function () {

  // GET /api/eventos - Catálogo de eventos publicados, con filtros y paginación
  Route::get('/', 'index');

  // GET /api/eventos/{id} - Ficha de un evento con sus tipos de entrada
  Route::get('/{evento}', 'show');
});


/**
 * RUTAS CATEGORIAS
 */
// GET /api/categorias - Listar las categorías para el filtro del catálogo
Route::get('/categorias', [CategoriaController::class, 'index']);


/**
 * RUTAS SESIÓN
 */
// POST /api/register - Alta de usuario, que devuelve ya su token
Route::post('/register', [AuthController::class, 'register']);

// POST /api/login - Iniciar sesión, reactivando la cuenta si estaba de baja
Route::post('/login', [AuthController::class, 'login']);
