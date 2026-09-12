<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
  public function run(): void {
    $this->call([
      CategoriaSeeder::class,
      EstadoEventoSeeder::class,
      EstadoCarritoSeeder::class,
      EstadoPagoSeeder::class,
      TipoFacturaSeeder::class,
      UsuarioSeeder::class,
      EventoSeeder::class,
      TipoEntradaSeeder::class,
      CarritoSeeder::class,
      DetalleCarritoSeeder::class,
      CompraSeeder::class,
      DetalleCompraSeeder::class,
      FacturaSeeder::class,
    ]);
  }
}
