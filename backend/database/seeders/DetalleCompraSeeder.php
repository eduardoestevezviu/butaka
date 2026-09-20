<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleCompraSeeder extends Seeder {
  public function run(): void {
    $u = DB::table('usuarios')->pluck('id_usuario', 'email');

    // Las compras pagadas se localizan por su fecha de pago, que es distinta en cada una
    $c = DB::table('compras')->whereNotNull('fecha_pago')->pluck('id_compra', 'fecha_pago');

    // Las pendientes no tienen fecha, pero cada asistente tiene como mucho una
    $pendienteLucia = DB::table('compras')->where('id_usuario', $u['lucia@asistente.com'])->whereNull('fecha_pago')->value('id_compra');
    $pendienteNerea = DB::table('compras')->where('id_usuario', $u['nerea@asistente.com'])->whereNull('fecha_pago')->value('id_compra');

    // Los tipos de entrada se identifican con el nombre del evento seguido del nombre del tipo
    $t = DB::table('tipo_entradas')
      ->join('eventos', 'eventos.id_evento', '=', 'tipo_entradas.id_evento')
      ->selectRaw("CONCAT(eventos.nombre, ' - ', tipo_entradas.nombre) as clave, tipo_entradas.id_tipo_entrada")
      ->pluck('id_tipo_entrada', 'clave');

    $detalles = [
      [
        'id_compra' => $c['2026-07-02 18:24:00'],
        'id_tipo_entrada' => $t['Verano en la azotea - General'],
        'cantidad' => 4,
        'precio_unitario' => 18.00,
      ],
      [
        'id_compra' => $c['2026-08-11 10:05:00'],
        'id_tipo_entrada' => $t['La casa de los vientos - Platea'],
        'cantidad' => 3,
        'precio_unitario' => 24.00,
      ],
      [
        'id_compra' => $c['2026-08-28 19:40:00'],
        'id_tipo_entrada' => $t['Cuarteto de cuerda Isla Azul - General'],
        'cantidad' => 2,
        'precio_unitario' => 20.00,
      ],
      [
        'id_compra' => $c['2026-09-01 09:12:00'],
        'id_tipo_entrada' => $t['El viaje de la tortuga Lia - Infantil'],
        'cantidad' => 4,
        'precio_unitario' => 9.00,
      ],
      [
        'id_compra' => $c['2026-09-05 17:30:00'],
        'id_tipo_entrada' => $t['Taller de iniciacion al timple - Plaza'],
        'cantidad' => 1,
        'precio_unitario' => 40.00,
      ],
      [
        'id_compra' => $pendienteLucia,
        'id_tipo_entrada' => $t['Atlantica Fest: sesion inaugural - Abono'],
        'cantidad' => 2,
        'precio_unitario' => 45.00,
      ],
      [
        'id_compra' => $c['2026-08-03 12:15:00'],
        'id_tipo_entrada' => $t['Atlantica Fest: sesion inaugural - Abono'],
        'cantidad' => 2,
        'precio_unitario' => 45.00,
      ],
      [
        'id_compra' => $c['2026-08-20 20:02:00'],
        'id_tipo_entrada' => $t['Cuarteto de cuerda Isla Azul - General'],
        'cantidad' => 1,
        'precio_unitario' => 20.00,
      ],
      [
        'id_compra' => $c['2026-09-06 11:48:00'],
        'id_tipo_entrada' => $t['Monologos de barrio - General'],
        'cantidad' => 2,
        'precio_unitario' => 15.00,
      ],
      [
        'id_compra' => $pendienteNerea,
        'id_tipo_entrada' => $t['Raices: danza contemporanea - Reducida'],
        'cantidad' => 2,
        'precio_unitario' => 15.00,
      ],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($detalles as $indice => $fila) {
      $detalles[$indice]['created_at'] = now();
      $detalles[$indice]['updated_at'] = now();
    }

    DB::table('detalle_compras')->insert($detalles);
  }
}
