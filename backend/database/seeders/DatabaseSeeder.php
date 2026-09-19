<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder {
  public function run(): void {
    // El cliente personal vive en oauth_clients, que migrate:fresh vacía. Crearlo
    // aquí evita tener que repetir el comando a mano tras cada reseteo
    Artisan::call('passport:client', [
      '--personal' => true,
      '--provider' => 'usuarios',
      '--no-interaction' => true,
    ]);

    $this->call([
      CategoriaSeeder::class,
      EstadoEventoSeeder::class,
      EstadoCarritoSeeder::class,
      EstadoPagoSeeder::class,
      TipoFacturaSeeder::class,
      RolSeeder::class,
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
