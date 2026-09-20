<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarritoSeeder extends Seeder {
  public function run(): void {
    $u = DB::table('usuarios')->pluck('id_usuario', 'email');

    // Carritos activos, para poder mostrar la cesta con contenido al iniciar sesion
    $carritos = [
      ['id_usuario' => $u['lucia@asistente.com'], 'id_estado_carrito' => 1],
      ['id_usuario' => $u['marcos@asistente.com'], 'id_estado_carrito' => 1],
    ];

    foreach ($carritos as $carrito) {
      $carrito['id_carrito'] = (string) Str::uuid();
      // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent
      $carrito['created_at'] = now();
      $carrito['updated_at'] = now();

      DB::table('carritos')->insert($carrito);
    }
  }
}
