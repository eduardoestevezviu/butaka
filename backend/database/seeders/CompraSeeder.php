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
      // Compra pendiente de pago, para mostrar ese estado en la demo. Son dos abonos
      // del Atlántica Fest a 38,25 euros, el precio del abono con su 15% de oferta
      [
        'id_usuario' => $u['lucia@asistente.com'],
        'id_estado_pago' => 1,
        'importe_total' => 76.50,
        'puntos_ganados' => 0,
        'puntos_canjeados' => 0,
        'fecha_pago' => null,
      ],
      // Marcos, con un canje de 100 puntos en su ultima compra
      [
        'id_usuario' => $u['marcos@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 76.50,
        'puntos_ganados' => 76,
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
      // Compras del resto de asistentes, que son las que dan volumen de ventas a
      // los eventos y permiten que el ranking de organizadores tenga algo que ordenar
      // Noche de boleros, con la entrada general a 12 euros por su 20% de oferta
      [
        'id_usuario' => $u['yaiza@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 72.00,
        'puntos_ganados' => 72,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-30 21:00:00',
      ],
      [
        'id_usuario' => $u['airam@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 72.00,
        'puntos_ganados' => 72,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-04 12:22:00',
      ],
      [
        'id_usuario' => $u['dacil@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 72.00,
        'puntos_ganados' => 72,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-07 18:45:00',
      ],
      [
        'id_usuario' => $u['nauzet@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 60.00,
        'puntos_ganados' => 60,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-12 09:40:00',
      ],
      // Cuarteto de cuerda Isla Azul
      [
        'id_usuario' => $u['yaiza@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 120.00,
        'puntos_ganados' => 120,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-10 09:55:00',
      ],
      [
        'id_usuario' => $u['guacimara@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 84.00,
        'puntos_ganados' => 84,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-14 19:18:00',
      ],
      // Atlántica Fest, con el abono a 38,25 euros por su 15% de oferta
      [
        'id_usuario' => $u['dacil@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 229.50,
        'puntos_ganados' => 229,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-06-26 18:20:00',
      ],
      [
        'id_usuario' => $u['guacimara@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 210.00,
        'puntos_ganados' => 210,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-01 11:02:00',
      ],
      [
        'id_usuario' => $u['jonay@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 270.00,
        'puntos_ganados' => 270,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-06 16:40:00',
      ],
      // La casa de los vientos
      [
        'id_usuario' => $u['airam@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 144.00,
        'puntos_ganados' => 144,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-21 13:30:00',
      ],
      [
        'id_usuario' => $u['jonay@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 96.00,
        'puntos_ganados' => 96,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-07-25 10:08:00',
      ],
      // Monólogos de barrio y Raíces: danza contemporánea
      [
        'id_usuario' => $u['idaira@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 90.00,
        'puntos_ganados' => 90,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-25 17:50:00',
      ],
      [
        'id_usuario' => $u['nauzet@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 132.00,
        'puntos_ganados' => 132,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-17 20:05:00',
      ],
      [
        'id_usuario' => $u['idaira@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 90.00,
        'puntos_ganados' => 90,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-21 11:15:00',
      ],
      // Miradas del Atlántico y Feria de artesanía, los dos eventos más económicos
      [
        'id_usuario' => $u['guacimara@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 30.00,
        'puntos_ganados' => 30,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-09-08 12:10:00',
      ],
      [
        'id_usuario' => $u['rayco@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 18.00,
        'puntos_ganados' => 18,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-09-20 11:40:00',
      ],
      // Taller de timple y curso de producción, con el curso a 67,50 euros por su 10% de oferta
      [
        'id_usuario' => $u['airam@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 240.00,
        'puntos_ganados' => 240,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-06-08 10:14:00',
      ],
      [
        'id_usuario' => $u['jonay@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 240.00,
        'puntos_ganados' => 240,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-06-15 09:30:00',
      ],
      [
        'id_usuario' => $u['rayco@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 405.00,
        'puntos_ganados' => 405,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-09-14 16:35:00',
      ],
      // El viaje de la tortuga Lía
      [
        'id_usuario' => $u['dacil@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 54.00,
        'puntos_ganados' => 54,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-08-29 10:30:00',
      ],
      [
        'id_usuario' => $u['idaira@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 72.00,
        'puntos_ganados' => 72,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-09-02 19:25:00',
      ],
      // Verano en la azotea, cuyas compras son anteriores a su celebración en julio
      [
        'id_usuario' => $u['yaiza@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 108.00,
        'puntos_ganados' => 108,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-06-12 17:05:00',
      ],
      [
        'id_usuario' => $u['nauzet@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 108.00,
        'puntos_ganados' => 108,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-06-18 20:12:00',
      ],
      [
        'id_usuario' => $u['rayco@asistente.com'],
        'id_estado_pago' => 2,
        'importe_total' => 48.00,
        'puntos_ganados' => 48,
        'puntos_canjeados' => 0,
        'fecha_pago' => '2026-06-22 12:45:00',
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
