<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder {
  public function run(): void {
    $registros = [
      ['id_categoria' => 1, 'categoria' => 'Música'],
      ['id_categoria' => 2, 'categoria' => 'Teatro'],
      ['id_categoria' => 3, 'categoria' => 'Danza'],
      ['id_categoria' => 4, 'categoria' => 'Exposiciones'],
      ['id_categoria' => 5, 'categoria' => 'Cursos y talleres'],
      ['id_categoria' => 6, 'categoria' => 'Familiar'],
      ['id_categoria' => 7, 'categoria' => 'Otros'],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($registros as $indice => $fila) {
      $registros[$indice]['created_at'] = now();
      $registros[$indice]['updated_at'] = now();
    }

    DB::table('categorias')->insert($registros);
  }
}
