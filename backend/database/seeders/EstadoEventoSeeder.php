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

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($registros as $indice => $fila) {
      $registros[$indice]['created_at'] = now();
      $registros[$indice]['updated_at'] = now();
    }

    DB::table('estado_eventos')->insert($registros);
  }
}
