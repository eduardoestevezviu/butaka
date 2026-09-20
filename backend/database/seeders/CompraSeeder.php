<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompraSeeder extends Seeder {
  public function run(): void {
    $u = DB::table('usuarios')->pluck('id_usuario', 'email');

    $compras = [
      // Lucia, historial completo de compras pagadas
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 72.00,
        'puntos_ganados' => 72,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-02 18:24:00',
      ],
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 72.00,
        'puntos_ganados' => 72,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-11 10:05:00',
      ],
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 40.00,
        'puntos_ganados' => 40,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-28 19:40:00',
      ],
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 36.00,
        'puntos_ganados' => 36,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-09-01 09:12:00',
      ],
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 40.00,
        'puntos_ganados' => 40,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-09-05 17:30:00',
      ],
      // Compra pendiente de pago, para mostrar ese estado en la demo
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 1,
        'importe_total' => 90.00,
        'puntos_ganados' => 0,
        'puntos_canjeados' => 0,
        'fecha_pago' => null,
      ],
      // Marcos, con un canje de 100 puntos en su ultima compra
      [
        'id_usuario' => $u['marcos@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 90.00,
        'puntos_ganados' => 90,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-03 12:15:00',
      ],
      [
        'id_usuario' => $u['marcos@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 20.00,
        'puntos_ganados' => 20,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-20 20:02:00',
      ],
      // 30 euros de entradas menos 5 euros por los 100 puntos canjeados
      [
        'id_usuario' => $u['marcos@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 25.00,
        'puntos_ganados' => 25,
        'puntos_canjeados' => 100,
        'fecha_pago' => '2026-09-06 11:48:00',
      ],
      [
        'id_usuario' => $u['nerea@asistente.com'],
        'id_estado_pago' => 1,
        'importe_total' => 30.00,
        'puntos_ganados' => 0,
        'puntos_canjeados' => 0,
        'fecha_pago' => null,
      ],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($compras as $indice => $fila) {
      $compras[$indice]['created_at'] = now();
      $compras[$indice]['updated_at'] = now();
    }

    DB::table('compras')->insert($compras);
  }
}
