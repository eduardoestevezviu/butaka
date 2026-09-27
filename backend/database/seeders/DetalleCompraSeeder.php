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
        'id_tipo_entrada' => $t['El viaje de la tortuga Lía - Infantil'],
        'cantidad' => 4,
        'precio_unitario' => 9.00,
      ],
      [
        'id_compra' => $c['2026-09-05 17:30:00'],
        'id_tipo_entrada' => $t['Taller de iniciación al timple - Plaza'],
        'cantidad' => 1,
        'precio_unitario' => 40.00,
      ],
      [
        'id_compra' => $pendienteLucia,
        'id_tipo_entrada' => $t['Atlántica Fest: sesión inaugural - Abono'],
        'cantidad' => 2,
        'precio_unitario' => 38.25,
      ],
      [
        'id_compra' => $c['2026-08-03 12:15:00'],
        'id_tipo_entrada' => $t['Atlántica Fest: sesión inaugural - Abono'],
        'cantidad' => 2,
        'precio_unitario' => 38.25,
      ],
      [
        'id_compra' => $c['2026-08-20 20:02:00'],
        'id_tipo_entrada' => $t['Cuarteto de cuerda Isla Azul - General'],
        'cantidad' => 1,
        'precio_unitario' => 20.00,
      ],
      [
        'id_compra' => $c['2026-09-06 11:48:00'],
        'id_tipo_entrada' => $t['Monólogos de barrio - General'],
        'cantidad' => 2,
        'precio_unitario' => 15.00,
      ],
      [
        'id_compra' => $pendienteNerea,
        'id_tipo_entrada' => $t['Raíces: danza contemporánea - Reducida'],
        'cantidad' => 2,
        'precio_unitario' => 15.00,
      ],
      // Entradas del resto de asistentes. Cada compra es de un solo evento y de seis
      // entradas como máximo, que es el límite por persona que impone la plataforma
      [
        'id_compra' => $c['2026-07-30 21:00:00'],
        'id_tipo_entrada' => $t['Noche de boleros con Aroa Santana - General'],
        'cantidad' => 6,
        'precio_unitario' => 12.00,
      ],
      [
        'id_compra' => $c['2026-08-04 12:22:00'],
        'id_tipo_entrada' => $t['Noche de boleros con Aroa Santana - General'],
        'cantidad' => 6,
        'precio_unitario' => 12.00,
      ],
      [
        'id_compra' => $c['2026-08-07 18:45:00'],
        'id_tipo_entrada' => $t['Noche de boleros con Aroa Santana - General'],
        'cantidad' => 6,
        'precio_unitario' => 12.00,
      ],
      [
        'id_compra' => $c['2026-08-12 09:40:00'],
        'id_tipo_entrada' => $t['Noche de boleros con Aroa Santana - Reducida'],
        'cantidad' => 6,
        'precio_unitario' => 10.00,
      ],
      [
        'id_compra' => $c['2026-07-10 09:55:00'],
        'id_tipo_entrada' => $t['Cuarteto de cuerda Isla Azul - General'],
        'cantidad' => 6,
        'precio_unitario' => 20.00,
      ],
      [
        'id_compra' => $c['2026-07-14 19:18:00'],
        'id_tipo_entrada' => $t['Cuarteto de cuerda Isla Azul - Reducida'],
        'cantidad' => 6,
        'precio_unitario' => 14.00,
      ],
      [
        'id_compra' => $c['2026-06-26 18:20:00'],
        'id_tipo_entrada' => $t['Atlántica Fest: sesión inaugural - Abono'],
        'cantidad' => 6,
        'precio_unitario' => 38.25,
      ],
      [
        'id_compra' => $c['2026-07-01 11:02:00'],
        'id_tipo_entrada' => $t['Atlántica Fest: sesión inaugural - Reducida'],
        'cantidad' => 6,
        'precio_unitario' => 35.00,
      ],
      [
        'id_compra' => $c['2026-07-06 16:40:00'],
        'id_tipo_entrada' => $t['Atlántica Fest: sesión inaugural - VIP'],
        'cantidad' => 3,
        'precio_unitario' => 90.00,
      ],
      [
        'id_compra' => $c['2026-07-21 13:30:00'],
        'id_tipo_entrada' => $t['La casa de los vientos - Platea'],
        'cantidad' => 6,
        'precio_unitario' => 24.00,
      ],
      [
        'id_compra' => $c['2026-07-25 10:08:00'],
        'id_tipo_entrada' => $t['La casa de los vientos - Anfiteatro'],
        'cantidad' => 6,
        'precio_unitario' => 16.00,
      ],
      [
        'id_compra' => $c['2026-08-25 17:50:00'],
        'id_tipo_entrada' => $t['Monólogos de barrio - General'],
        'cantidad' => 6,
        'precio_unitario' => 15.00,
      ],
      [
        'id_compra' => $c['2026-08-17 20:05:00'],
        'id_tipo_entrada' => $t['Raíces: danza contemporánea - General'],
        'cantidad' => 6,
        'precio_unitario' => 22.00,
      ],
      [
        'id_compra' => $c['2026-08-21 11:15:00'],
        'id_tipo_entrada' => $t['Raíces: danza contemporánea - Reducida'],
        'cantidad' => 6,
        'precio_unitario' => 15.00,
      ],
      [
        'id_compra' => $c['2026-09-08 12:10:00'],
        'id_tipo_entrada' => $t['Miradas del Atlántico - Entrada general'],
        'cantidad' => 6,
        'precio_unitario' => 5.00,
      ],
      [
        'id_compra' => $c['2026-06-08 10:14:00'],
        'id_tipo_entrada' => $t['Taller de iniciación al timple - Plaza'],
        'cantidad' => 6,
        'precio_unitario' => 40.00,
      ],
      [
        'id_compra' => $c['2026-06-15 09:30:00'],
        'id_tipo_entrada' => $t['Taller de iniciación al timple - Plaza'],
        'cantidad' => 6,
        'precio_unitario' => 40.00,
      ],
      [
        'id_compra' => $c['2026-09-14 16:35:00'],
        'id_tipo_entrada' => $t['Curso de producción de eventos musicales - Plaza'],
        'cantidad' => 6,
        'precio_unitario' => 67.50,
      ],
      [
        'id_compra' => $c['2026-08-29 10:30:00'],
        'id_tipo_entrada' => $t['El viaje de la tortuga Lía - Infantil'],
        'cantidad' => 6,
        'precio_unitario' => 9.00,
      ],
      [
        'id_compra' => $c['2026-09-02 19:25:00'],
        'id_tipo_entrada' => $t['El viaje de la tortuga Lía - Adulto'],
        'cantidad' => 6,
        'precio_unitario' => 12.00,
      ],
      [
        'id_compra' => $c['2026-09-20 11:40:00'],
        'id_tipo_entrada' => $t['Feria de artesanía y música de barrio - Entrada general'],
        'cantidad' => 6,
        'precio_unitario' => 3.00,
      ],
      [
        'id_compra' => $c['2026-06-12 17:05:00'],
        'id_tipo_entrada' => $t['Verano en la azotea - General'],
        'cantidad' => 6,
        'precio_unitario' => 18.00,
      ],
      [
        'id_compra' => $c['2026-06-18 20:12:00'],
        'id_tipo_entrada' => $t['Verano en la azotea - General'],
        'cantidad' => 6,
        'precio_unitario' => 18.00,
      ],
      [
        'id_compra' => $c['2026-06-22 12:45:00'],
        'id_tipo_entrada' => $t['Verano en la azotea - Reducida'],
        'cantidad' => 4,
        'precio_unitario' => 12.00,
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
