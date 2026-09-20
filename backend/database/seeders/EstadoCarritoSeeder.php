<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoCarritoSeeder extends Seeder {
  public function run(): void {
    $registros = [
      ['id_estado_carrito' => 1, 'estado_carrito' => 'Activo'],
      ['id_estado_carrito' => 2, 'estado_carrito' => 'Convertido'],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($registros as $indice => $fila) {
      $registros[$indice]['created_at'] = now();
      $registros[$indice]['updated_at'] = now();
    }

    DB::table('estado_carritos')->insert($registros);
  }
}
