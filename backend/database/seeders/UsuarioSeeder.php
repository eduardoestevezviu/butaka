<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Usuario;
use Spatie\Permission\Models\Role;

class UsuarioSeeder extends Seeder {
  public function run(): void {
    $alba = (string) Str::uuid();
    $ignacio = (string) Str::uuid();
    $candelaria = (string) Str::uuid();
    $hector = (string) Str::uuid();
    $lucia = (string) Str::uuid();
    $marcos = (string) Str::uuid();
    $nerea = (string) Str::uuid();

    $usuarios = [
      [
        'id_usuario' => $alba,
        'nombre' => 'Alba',
        'apellidos' => 'Déniz Suárez',
        'email' => 'alba@salatimple.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '928110022',
        'calle' => 'Calle Perojo',
        'vivienda' => '14',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35003',
      ],
      [
        'id_usuario' => $ignacio,
        'nombre' => 'Ignacio',
        'apellidos' => 'Marrero Cabrera',
        'email' => 'ignacio@teatrobahia.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '922330144',
        'calle' => 'Avenida Marítima',
        'vivienda' => '3',
        'ciudad' => 'Santa Cruz de Tenerife',
        'codigo_postal' => '38001',
      ],
      [
        'id_usuario' => $candelaria,
        'nombre' => 'Candelaria',
        'apellidos' => 'Ojeda Ramos',
        'email' => 'candelaria@barrancovivo.org',
        'password' => Hash::make('password'),
        'numero_telefono' => '928445566',
        'calle' => 'Calle Alfredo Calderón',
        'vivienda' => '8',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35007',
      ],
      [
        'id_usuario' => $hector,
        'nombre' => 'Héctor',
        'apellidos' => 'Quintana Alonso',
        'email' => 'hector@atlanticalive.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '928778899',
        'calle' => 'Calle Triana',
        'vivienda' => '102',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35002',
      ],
      [
        'id_usuario' => $lucia,
        'nombre' => 'Lucía',
        'apellidos' => 'Rodríguez Betancor',
        'email' => 'lucia@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600112233',
        'calle' => 'Calle Venegas',
        'vivienda' => '22',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35003',
      ],
      [
        'id_usuario' => $marcos,
        'nombre' => 'Marcos',
        'apellidos' => 'Santana Pérez',
        'email' => 'marcos@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600445566',
        'calle' => 'Calle Néstor de la Torre',
        'vivienda' => '5',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35006',
      ],
      [
        'id_usuario' => $nerea,
        'nombre' => 'Nerea',
        'apellidos' => 'González Medina',
        'email' => 'nerea@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600778899',
        'calle' => 'Calle La Marina',
        'vivienda' => '9',
        'ciudad' => 'Santa Cruz de Tenerife',
        'codigo_postal' => '38002',
      ],
    ];

    DB::table('usuarios')->insert($usuarios);

    // Todo usuario es asistente, ya que cualquiera puede comprar entradas
    $asistentes = [
      ['id_usuario' => $alba, 'puntos' => 0],
      ['id_usuario' => $ignacio, 'puntos' => 0],
      ['id_usuario' => $candelaria, 'puntos' => 0],
      ['id_usuario' => $hector, 'puntos' => 0],
      ['id_usuario' => $lucia, 'puntos' => 260],
      ['id_usuario' => $marcos, 'puntos' => 35],
      ['id_usuario' => $nerea, 'puntos' => 0],
    ];

    DB::table('asistentes')->insert($asistentes);

    // Cuatro de los usuarios han aportado sus datos fiscales para poder publicar eventos
    $organizadores = [
      [
        'id_usuario' => $alba,
        'nombre_comercial' => 'Sala Timple',
        'nif' => '44567812P',
        'iban' => 'ES9121000418450200051332',
      ],
      [
        'id_usuario' => $ignacio,
        'nombre_comercial' => 'Teatro Bahía',
        'nif' => 'B38445566',
        'iban' => 'ES7620770024003102575766',
      ],
      [
        'id_usuario' => $candelaria,
        'nombre_comercial' => 'Asociación Barranco Vivo',
        'nif' => 'G35778899',
        'iban' => 'ES6000491500051234567892',
      ],
      [
        'id_usuario' => $hector,
        'nombre_comercial' => 'Atlántica Live',
        'nif' => 'B35334455',
        'iban' => 'ES1000492352082414205416',
      ],
    ];

    DB::table('organizadores')->insert($organizadores);

    // Los roles se asignan con Eloquent, ya que Spatie escribe en su propia
    // tabla intermedia y no basta con insertar en usuarios
    $rolAsistente = Role::findByName('asistente', 'api');
    $rolOrganizador = Role::findByName('organizador', 'api');

    Usuario::all()->each->assignRole($rolAsistente);

    Usuario::whereIn('id_usuario', [$alba, $ignacio, $candelaria, $hector])
      ->get()
      ->each
      ->assignRole($rolOrganizador);
  }
}
