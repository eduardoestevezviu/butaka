<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacturaSeeder extends Seeder {
  public function run(): void {
    $u = DB::table('usuarios')->pluck('id_usuario', 'email');
    $c = DB::table('compras')->whereNotNull('fecha_pago')->pluck('id_compra', 'fecha_pago');
    $e = DB::table('eventos')->pluck('id_evento', 'nombre');

    // Comprobantes al asistente, con el IVA reducido del 10% de los espectaculos en vivo
    $facturas = [
      [
        'numero_factura' => 'C-20260702-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['lucia@asistente.com'],
        'fecha_emision' => '2026-07-02',
        'base_imponible' => 65.45,
        'iva' => 6.55,
        'total' => 72.00,
        'id_compra' => $c['2026-07-02 18:24:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260803-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['marcos@asistente.com'],
        'fecha_emision' => '2026-08-03',
        'base_imponible' => 81.82,
        'iva' => 8.18,
        'total' => 90.00,
        'id_compra' => $c['2026-08-03 12:15:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260811-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['lucia@asistente.com'],
        'fecha_emision' => '2026-08-11',
        'base_imponible' => 65.45,
        'iva' => 6.55,
        'total' => 72.00,
        'id_compra' => $c['2026-08-11 10:05:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260820-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['marcos@asistente.com'],
        'fecha_emision' => '2026-08-20',
        'base_imponible' => 18.18,
        'iva' => 1.82,
        'total' => 20.00,
        'id_compra' => $c['2026-08-20 20:02:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260828-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['lucia@asistente.com'],
        'fecha_emision' => '2026-08-28',
        'base_imponible' => 36.36,
        'iva' => 3.64,
        'total' => 40.00,
        'id_compra' => $c['2026-08-28 19:40:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260901-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['lucia@asistente.com'],
        'fecha_emision' => '2026-09-01',
        'base_imponible' => 32.73,
        'iva' => 3.27,
        'total' => 36.00,
        'id_compra' => $c['2026-09-01 09:12:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260905-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['lucia@asistente.com'],
        'fecha_emision' => '2026-09-05',
        'base_imponible' => 36.36,
        'iva' => 3.64,
        'total' => 40.00,
        'id_compra' => $c['2026-09-05 17:30:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      [
        'numero_factura' => 'C-20260906-0001',
        'id_tipo_factura' => 1,
        'id_usuario' => $u['marcos@asistente.com'],
        'fecha_emision' => '2026-09-06',
        'base_imponible' => 22.73,
        'iva' => 2.27,
        'total' => 25.00,
        'id_compra' => $c['2026-09-06 11:48:00'],
        'id_evento' => null,
        'importe_bruto' => null,
        'comision' => null,
      ],
      // Liquidacion del unico evento ya celebrado, con 72 euros recaudados
      // La comision del 10% son 7,20 euros de base, mas el 21% de IVA del servicio
      [
        'numero_factura' => 'L-20260720-0001',
        'id_tipo_factura' => 2,
        'id_usuario' => $u['alba@salatimple.com'],
        'fecha_emision' => '2026-07-20',
        'base_imponible' => 7.20,
        'iva' => 1.51,
        'total' => 8.71,
        'id_compra' => null,
        'id_evento' => $e['Verano en la azotea'],
        'importe_bruto' => 72.00,
        'comision' => 10,
      ],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($facturas as $indice => $fila) {
      $facturas[$indice]['created_at'] = now();
      $facturas[$indice]['updated_at'] = now();
    }

    DB::table('facturas')->insert($facturas);
  }
}
