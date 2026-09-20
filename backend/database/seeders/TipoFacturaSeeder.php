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

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($registros as $indice => $fila) {
      $registros[$indice]['created_at'] = now();
      $registros[$indice]['updated_at'] = now();
    }

    DB::table('tipo_facturas')->insert($registros);
  }
}
