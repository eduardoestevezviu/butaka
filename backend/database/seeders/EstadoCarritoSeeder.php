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

    DB::table('estado_carritos')->insert($registros);
  }
}
