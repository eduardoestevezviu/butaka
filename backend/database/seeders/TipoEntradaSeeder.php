<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TipoEntradaSeeder extends Seeder {
  public function run(): void {
    // Se recuperan los eventos por su nombre para no depender de los UUID generados
    $e = DB::table('eventos')->pluck('id_evento', 'nombre');

    $tipos = [
      ['evento' => 'Noche de boleros con Aroa Santana', 'nombre' => 'General', 'precio' => 15.00, 'aforo' => 120, 'oferta' => 20, 'vendidas' => 0],
      ['evento' => 'Noche de boleros con Aroa Santana', 'nombre' => 'Reducida', 'precio' => 10.00, 'aforo' => 30, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Cuarteto de cuerda Isla Azul', 'nombre' => 'General', 'precio' => 20.00, 'aforo' => 180, 'oferta' => null, 'vendidas' => 3],
      ['evento' => 'Cuarteto de cuerda Isla Azul', 'nombre' => 'Reducida', 'precio' => 14.00, 'aforo' => 60, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Atlántica Fest: sesión inaugural', 'nombre' => 'Abono', 'precio' => 45.00, 'aforo' => 500, 'oferta' => 15, 'vendidas' => 2],
      ['evento' => 'Atlántica Fest: sesión inaugural', 'nombre' => 'Reducida', 'precio' => 35.00, 'aforo' => 100, 'oferta' => null, 'vendidas' => 0],
      ['evento' => 'Atlántica Fest: sesión inaugural', 'nombre' => 'VIP', 'precio' => 90.00, 'aforo' => 50, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'La casa de los vientos', 'nombre' => 'Platea', 'precio' => 24.00, 'aforo' => 200, 'oferta' => null, 'vendidas' => 3],
      ['evento' => 'La casa de los vientos', 'nombre' => 'Anfiteatro', 'precio' => 16.00, 'aforo' => 150, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Monólogos de barrio', 'nombre' => 'General', 'precio' => 15.00, 'aforo' => 80, 'oferta' => null, 'vendidas' => 2],

      ['evento' => 'Raíces: danza contemporánea', 'nombre' => 'General', 'precio' => 22.00, 'aforo' => 200, 'oferta' => null, 'vendidas' => 0],
      ['evento' => 'Raíces: danza contemporánea', 'nombre' => 'Reducida', 'precio' => 15.00, 'aforo' => 60, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Miradas del Atlántico', 'nombre' => 'Entrada general', 'precio' => 5.00, 'aforo' => 300, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Taller de iniciación al timple', 'nombre' => 'Plaza', 'precio' => 40.00, 'aforo' => 20, 'oferta' => null, 'vendidas' => 1],

      ['evento' => 'Curso de producción de eventos musicales', 'nombre' => 'Plaza', 'precio' => 75.00, 'aforo' => 30, 'oferta' => 10, 'vendidas' => 0],

      ['evento' => 'El viaje de la tortuga Lía', 'nombre' => 'Infantil', 'precio' => 9.00, 'aforo' => 100, 'oferta' => null, 'vendidas' => 4],
      ['evento' => 'El viaje de la tortuga Lía', 'nombre' => 'Adulto', 'precio' => 12.00, 'aforo' => 100, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Feria de artesanía y música de barrio', 'nombre' => 'Entrada general', 'precio' => 3.00, 'aforo' => 500, 'oferta' => null, 'vendidas' => 0],

      ['evento' => 'Verano en la azotea', 'nombre' => 'General', 'precio' => 18.00, 'aforo' => 120, 'oferta' => null, 'vendidas' => 4],
      ['evento' => 'Verano en la azotea', 'nombre' => 'Reducida', 'precio' => 12.00, 'aforo' => 40, 'oferta' => null, 'vendidas' => 0],
    ];

    foreach ($tipos as $tipo) {
      DB::table('tipo_entradas')->insert([
        'id_tipo_entrada' => (string) Str::uuid(),
        'nombre' => $tipo['nombre'],
        'precio' => $tipo['precio'],
        'oferta' => $tipo['oferta'],
        'aforo' => $tipo['aforo'],
        'vendidas' => $tipo['vendidas'],
        'id_evento' => $e[$tipo['evento']],
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
