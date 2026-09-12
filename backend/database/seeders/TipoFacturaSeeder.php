<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoFacturaSeeder extends Seeder {
  public function run(): void {
    $registros = [
      ['id_tipo_factura' => 1, 'tipo_factura' => 'Compra'],
      ['id_tipo_factura' => 2, 'tipo_factura' => 'Liquidación'],
    ];

    DB::table('tipo_facturas')->insert($registros);
  }
}
