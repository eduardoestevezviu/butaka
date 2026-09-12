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

    DB::table('estado_pagos')->insert($registros);
  }
}
