<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventoSeeder extends Seeder {
  public function run(): void {
    // Se recuperan los organizadores por su correo para no depender de los UUID generados
    $u = DB::table('usuarios')->pluck('id_usuario', 'email');

    $sala = $u['alba@salatimple.com'];
    $teatro = $u['ignacio@teatrobahia.com'];
    $asociacion = $u['candelaria@barrancovivo.org'];
    $promotora = $u['hector@atlanticalive.com'];

    // Las fechas se calculan desde el día en que se ejecuta el seeder, para que los
    // eventos sigan en cartelera cada vez que se rehace la base de datos. El único
    // con fecha fija es el ya celebrado, del que depende la factura de liquidación
    //
    // Cada evento guarda la URL de su imagen. Hasta tener las definitivas, todos
    // usan la imagen por defecto que sirve el propio backend
    $eventos = [
      [
        'nombre' => 'Noche de boleros con Aroa Santana',
        'descripcion' => 'Un recorrido íntimo por el bolero clásico y sus versiones más actuales, acompañado al piano.',
        'ubicacion' => 'Sala Timple, Calle Perojo 14, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(12)->setTime(21, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $sala,
        'id_categoria' => 1,
      ],
      [
        'nombre' => 'Cuarteto de cuerda Isla Azul',
        'descripcion' => 'Programa dedicado a compositores canarios del siglo XX, con obras poco interpretadas.',
        'ubicacion' => 'Teatro Bahía, Avenida Marítima 3, Santa Cruz de Tenerife',
        'fecha' => now()->addDays(34)->setTime(19, 30),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $teatro,
        'id_categoria' => 1,
      ],
      [
        'nombre' => 'Atlántica Fest: sesión inaugural',
        'descripcion' => 'Primera jornada del festival, con tres bandas emergentes del archipiélago y sesión de cierre.',
        'ubicacion' => 'Recinto Ferial, Avenida de la Feria 1, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(54)->setTime(20, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $promotora,
        'id_categoria' => 1,
      ],
      [
        'nombre' => 'La casa de los vientos',
        'descripcion' => 'Drama contemporáneo sobre tres generaciones de una familia en un pueblo del norte.',
        'ubicacion' => 'Teatro Bahía, Avenida Marítima 3, Santa Cruz de Tenerife',
        'fecha' => now()->addDays(5)->setTime(20, 30),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $teatro,
        'id_categoria' => 2,
      ],
      [
        'nombre' => 'Monólogos de barrio',
        'descripcion' => 'Cuatro humoristas locales presentan material nuevo en formato corto.',
        'ubicacion' => 'Centro Vecinal Barranco Vivo, Calle Alfredo Calderón 8, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(19)->setTime(21, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $asociacion,
        'id_categoria' => 2,
      ],
      [
        'nombre' => 'Raíces: danza contemporánea',
        'descripcion' => 'Pieza coreográfica que reinterpreta el folclore insular desde el lenguaje contemporáneo.',
        'ubicacion' => 'Teatro Bahía, Avenida Marítima 3, Santa Cruz de Tenerife',
        'fecha' => now()->addDays(47)->setTime(20, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $teatro,
        'id_categoria' => 3,
      ],
      [
        'nombre' => 'Miradas del Atlántico',
        'descripcion' => 'Exposición colectiva de fotografía documental sobre la vida en la costa.',
        'ubicacion' => 'Centro Vecinal Barranco Vivo, Calle Alfredo Calderón 8, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(3)->setTime(11, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $asociacion,
        'id_categoria' => 4,
      ],
      [
        'nombre' => 'Taller de iniciación al timple',
        'descripcion' => 'Sesión práctica de tres horas para principiantes. Se presta instrumento a quien lo necesite.',
        'ubicacion' => 'Sala Timple, Calle Perojo 14, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(27)->setTime(17, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $sala,
        'id_categoria' => 5,
      ],
      [
        'nombre' => 'Curso de producción de eventos musicales',
        'descripcion' => 'Jornada formativa sobre contratación, permisos y producción técnica de conciertos.',
        'ubicacion' => 'Calle Triana 102, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(61)->setTime(10, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $promotora,
        'id_categoria' => 5,
      ],
      [
        'nombre' => 'El viaje de la tortuga Lía',
        'descripcion' => 'Teatro de títeres para público infantil, con taller posterior de manualidades.',
        'ubicacion' => 'Teatro Bahía, Avenida Marítima 3, Santa Cruz de Tenerife',
        'fecha' => now()->addDays(13)->setTime(12, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $teatro,
        'id_categoria' => 6,
      ],
      [
        'nombre' => 'Feria de artesanía y música de barrio',
        'descripcion' => 'Jornada de puestos de artesanía local con actuaciones acústicas durante toda la mañana.',
        'ubicacion' => 'Plaza del Barranco, Las Palmas de Gran Canaria',
        'fecha' => now()->addDays(75)->setTime(11, 0),
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $asociacion,
        'id_categoria' => 7,
      ],
      // Evento ya celebrado, necesario para poder mostrar la liquidacion de un evento finalizado
      [
        'nombre' => 'Verano en la azotea',
        'descripcion' => 'Ciclo de conciertos acústicos al aire libre celebrado durante el mes de julio.',
        'ubicacion' => 'Sala Timple, Calle Perojo 14, Las Palmas de Gran Canaria',
        'fecha' => '2026-07-19 21:30:00',
        'imagen' => 'img/evento-por-defecto.jpg',
        'id_usuario' => $sala,
        'id_categoria' => 1,
      ],
    ];

    foreach ($eventos as $evento) {
      $evento['id_evento'] = (string) Str::uuid();
      // DB::table() no rellena las marcas de tiempo, eso lo hace Eloquent
      $evento['created_at'] = now();
      $evento['updated_at'] = now();

      DB::table('eventos')->insert($evento);
    }
  }
}
