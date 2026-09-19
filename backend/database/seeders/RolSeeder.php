<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolSeeder extends Seeder {
  public function run(): void {
    // El guard debe ser api, el mismo con el que se autentican los usuarios. Un
    // rol creado con el guard web por defecto existiría en la tabla, pero
    // hasRole() devolvería falso al comprobarlo desde un token de Passport
    Role::create(['name' => 'asistente', 'guard_name' => 'api']);
    Role::create(['name' => 'organizador', 'guard_name' => 'api']);
  }
}
