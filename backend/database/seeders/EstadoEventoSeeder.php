<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoEventoSeeder extends Seeder {
  public function run(): void {
    $registros = [
      ['id_estado_evento' => 1, 'estado_evento' => 'Publicado'],
      ['id_estado_evento' => 2, 'estado_evento' => 'Cancelado'],
    ];

    DB::table('estado_eventos')->insert($registros);
  }
}
