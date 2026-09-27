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
    $yaiza = (string) Str::uuid();
    $airam = (string) Str::uuid();
    $dacil = (string) Str::uuid();
    $nauzet = (string) Str::uuid();
    $guacimara = (string) Str::uuid();
    $jonay = (string) Str::uuid();
    $idaira = (string) Str::uuid();
    $rayco = (string) Str::uuid();

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
      // Resto de asistentes, cuyas compras dan volumen de ventas a los eventos
      [
        'id_usuario' => $yaiza,
        'nombre' => 'Yaiza',
        'apellidos' => 'Perdomo Falcón',
        'email' => 'yaiza@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600223344',
        'calle' => 'Calle Mesa y López',
        'vivienda' => '48',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35010',
      ],
      [
        'id_usuario' => $airam,
        'nombre' => 'Airam',
        'apellidos' => 'Cabrera Viera',
        'email' => 'airam@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600334455',
        'calle' => 'Calle Castillo',
        'vivienda' => '31',
        'ciudad' => 'Santa Cruz de Tenerife',
        'codigo_postal' => '38003',
      ],
      [
        'id_usuario' => $dacil,
        'nombre' => 'Dácil',
        'apellidos' => 'Betancort Ramírez',
        'email' => 'dacil@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600556677',
        'calle' => 'Calle Obispo Rey Redondo',
        'vivienda' => '7',
        'ciudad' => 'San Cristóbal de La Laguna',
        'codigo_postal' => '38201',
      ],
      [
        'id_usuario' => $nauzet,
        'nombre' => 'Nauzet',
        'apellidos' => 'Almeida Dorta',
        'email' => 'nauzet@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600667788',
        'calle' => 'Calle León y Castillo',
        'vivienda' => '120',
        'ciudad' => 'Telde',
        'codigo_postal' => '35200',
      ],
      [
        'id_usuario' => $guacimara,
        'nombre' => 'Guacimara',
        'apellidos' => 'Trujillo Suárez',
        'email' => 'guacimara@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600889900',
        'calle' => 'Calle Bravo Murillo',
        'vivienda' => '16',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35002',
      ],
      [
        'id_usuario' => $jonay,
        'nombre' => 'Jonay',
        'apellidos' => 'Padrón Melián',
        'email' => 'jonay@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '600990011',
        'calle' => 'Calle Doctor Guigou',
        'vivienda' => '4',
        'ciudad' => 'Santa Cruz de Tenerife',
        'codigo_postal' => '38001',
      ],
      [
        'id_usuario' => $idaira,
        'nombre' => 'Idaira',
        'apellidos' => 'Santana Cruz',
        'email' => 'idaira@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '601112233',
        'calle' => 'Calle Juan Rejón',
        'vivienda' => '55',
        'ciudad' => 'Las Palmas de Gran Canaria',
        'codigo_postal' => '35008',
      ],
      [
        'id_usuario' => $rayco,
        'nombre' => 'Rayco',
        'apellidos' => 'Hernández Bethencourt',
        'email' => 'rayco@asistente.com',
        'password' => Hash::make('password'),
        'numero_telefono' => '601223344',
        'calle' => 'Avenida de Anaga',
        'vivienda' => '27',
        'ciudad' => 'Santa Cruz de Tenerife',
        'codigo_postal' => '38001',
      ],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($usuarios as $indice => $fila) {
      $usuarios[$indice]['created_at'] = now();
      $usuarios[$indice]['updated_at'] = now();
    }

    DB::table('usuarios')->insert($usuarios);

    // Todo usuario es asistente, ya que cualquiera puede comprar entradas
    $asistentes = [
      ['id_usuario' => $alba, 'puntos' => 0],
      ['id_usuario' => $ignacio, 'puntos' => 0],
      ['id_usuario' => $candelaria, 'puntos' => 0],
      ['id_usuario' => $hector, 'puntos' => 0],
      ['id_usuario' => $lucia, 'puntos' => 260],
      ['id_usuario' => $marcos, 'puntos' => 21],
      ['id_usuario' => $nerea, 'puntos' => 0],
      ['id_usuario' => $yaiza, 'puntos' => 300],
      ['id_usuario' => $airam, 'puntos' => 456],
      ['id_usuario' => $dacil, 'puntos' => 355],
      ['id_usuario' => $nauzet, 'puntos' => 300],
      ['id_usuario' => $guacimara, 'puntos' => 324],
      ['id_usuario' => $jonay, 'puntos' => 606],
      ['id_usuario' => $idaira, 'puntos' => 252],
      ['id_usuario' => $rayco, 'puntos' => 471],
    ];

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($asistentes as $indice => $fila) {
      $asistentes[$indice]['created_at'] = now();
      $asistentes[$indice]['updated_at'] = now();
    }

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

    // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent,
    // de modo que se añaden a mano para no dejar filas sin fecha de alta
    foreach ($organizadores as $indice => $fila) {
      $organizadores[$indice]['created_at'] = now();
      $organizadores[$indice]['updated_at'] = now();
    }

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
