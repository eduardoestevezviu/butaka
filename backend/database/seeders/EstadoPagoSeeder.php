<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoPagoSeeder extends Seeder {
  public function run(): void {
    $registros = [
      ['id_estado_pago' => 1, 'estado_pago' => 'Pendiente'],
      ['id_estado_pago' => 2, 'estado_pago' => 'Completado'],
      ['id_estado_pago' => 3, 'estado_pago' => 'Reembolsado'],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($registros as $indice => $fila) {
      $registros[$indice]['created_at'] = now();
      $registros[$indice]['updated_at'] = now();
    }

    DB::table('estado_pagos')->insert($registros);
  }
}
