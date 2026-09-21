<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleCarritoSeeder extends Seeder {
  public function run(): void {
    $u = DB::table('usuarios')->pluck('id_usuario', 'email');

    // Cada asistente tiene un unico carrito activo, por lo que basta con localizarlo por usuario
    $carritoLucia = DB::table('carritos')->where('id_usuario', $u['lucia@asistente.com'])->value('id_carrito');
    $carritoMarcos = DB::table('carritos')->where('id_usuario', $u['marcos@asistente.com'])->value('id_carrito');

    // Los tipos de entrada se identifican con el nombre del evento seguido del nombre del tipo
    $t = DB::table('tipo_entradas')
      ->join('eventos', 'eventos.id_evento', '=', 'tipo_entradas.id_evento')
      ->selectRaw("CONCAT(eventos.nombre, ' - ', tipo_entradas.nombre) as clave, tipo_entradas.id_tipo_entrada")
      ->pluck('id_tipo_entrada', 'clave');

    $detalles = [
      [
        'id_carrito' => $carritoLucia,
        'id_tipo_entrada' => $t['Raíces: danza contemporánea - General'],
        'cantidad' => 2,
        'precio_unitario' => 22.00,
      ],
      [
        'id_carrito' => $carritoLucia,
        'id_tipo_entrada' => $t['Feria de artesanía y música de barrio - Entrada general'],
        'cantidad' => 3,
        'precio_unitario' => 3.00,
      ],
      [
        'id_carrito' => $carritoMarcos,
        'id_tipo_entrada' => $t['Miradas del Atlántico - Entrada general'],
        'cantidad' => 2,
        'precio_unitario' => 5.00,
      ],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($detalles as $indice => $fila) {
      $detalles[$indice]['created_at'] = now();
      $detalles[$indice]['updated_at'] = now();
    }

    DB::table('detalle_carritos')->insert($detalles);
  }
}
