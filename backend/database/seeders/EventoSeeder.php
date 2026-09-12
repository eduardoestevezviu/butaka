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

    // PENDIENTE se sustituye por la URL de la imagen de cada evento
    $eventos = [
      [
        'nombre' => 'Noche de boleros con Aroa Santana',
        'descripcion' => 'Un recorrido intimo por el bolero clasico y sus versiones mas actuales, acompanado al piano.',
        'ubicacion' => 'Sala Timple, Calle Perojo 14, Las Palmas de Gran Canaria',
        'fecha' => '2026-10-03 21:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $sala,
        'id_categoria' => 1,
      ],
      [
        'nombre' => 'Cuarteto de cuerda Isla Azul',
        'descripcion' => 'Programa dedicado a compositores canarios del siglo XX, con obras poco interpretadas.',
        'ubicacion' => 'Teatro Bahia, Avenida Maritima 3, Santa Cruz de Tenerife',
        'fecha' => '2026-10-25 19:30:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $teatro,
        'id_categoria' => 1,
      ],
      [
        'nombre' => 'Atlantica Fest: sesion inaugural',
        'descripcion' => 'Primera jornada del festival, con tres bandas emergentes del archipielago y sesion de cierre.',
        'ubicacion' => 'Recinto Ferial, Avenida de la Feria 1, Las Palmas de Gran Canaria',
        'fecha' => '2026-11-14 20:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $promotora,
        'id_categoria' => 1,
      ],
      [
        'nombre' => 'La casa de los vientos',
        'descripcion' => 'Drama contemporaneo sobre tres generaciones de una familia en un pueblo del norte.',
        'ubicacion' => 'Teatro Bahia, Avenida Maritima 3, Santa Cruz de Tenerife',
        'fecha' => '2026-09-26 20:30:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $teatro,
        'id_categoria' => 2,
      ],
      [
        'nombre' => 'Monologos de barrio',
        'descripcion' => 'Cuatro humoristas locales presentan material nuevo en formato corto.',
        'ubicacion' => 'Centro Vecinal Barranco Vivo, Calle Alfredo Calderon 8, Las Palmas de Gran Canaria',
        'fecha' => '2026-10-10 21:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $asociacion,
        'id_categoria' => 2,
      ],
      [
        'nombre' => 'Raices: danza contemporanea',
        'descripcion' => 'Pieza coreografica que reinterpreta el folclore insular desde el lenguaje contemporaneo.',
        'ubicacion' => 'Teatro Bahia, Avenida Maritima 3, Santa Cruz de Tenerife',
        'fecha' => '2026-11-07 20:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $teatro,
        'id_categoria' => 3,
      ],
      [
        'nombre' => 'Miradas del Atlantico',
        'descripcion' => 'Exposicion colectiva de fotografia documental sobre la vida en la costa.',
        'ubicacion' => 'Centro Vecinal Barranco Vivo, Calle Alfredo Calderon 8, Las Palmas de Gran Canaria',
        'fecha' => '2026-09-20 11:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $asociacion,
        'id_categoria' => 4,
      ],
      [
        'nombre' => 'Taller de iniciacion al timple',
        'descripcion' => 'Sesion practica de tres horas para principiantes. Se presta instrumento a quien lo necesite.',
        'ubicacion' => 'Sala Timple, Calle Perojo 14, Las Palmas de Gran Canaria',
        'fecha' => '2026-10-18 17:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $sala,
        'id_categoria' => 5,
      ],
      [
        'nombre' => 'Curso de produccion de eventos musicales',
        'descripcion' => 'Jornada formativa sobre contratacion, permisos y produccion tecnica de conciertos.',
        'ubicacion' => 'Calle Triana 102, Las Palmas de Gran Canaria',
        'fecha' => '2026-11-21 10:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $promotora,
        'id_categoria' => 5,
      ],
      [
        'nombre' => 'El viaje de la tortuga Lia',
        'descripcion' => 'Teatro de titeres para publico infantil, con taller posterior de manualidades.',
        'ubicacion' => 'Teatro Bahia, Avenida Maritima 3, Santa Cruz de Tenerife',
        'fecha' => '2026-10-04 12:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $teatro,
        'id_categoria' => 6,
      ],
      [
        'nombre' => 'Feria de artesania y musica de barrio',
        'descripcion' => 'Jornada de puestos de artesania local con actuaciones acusticas durante toda la manana.',
        'ubicacion' => 'Plaza del Barranco, Las Palmas de Gran Canaria',
        'fecha' => '2026-12-05 11:00:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $asociacion,
        'id_categoria' => 7,
      ],
      // Evento ya celebrado, necesario para poder mostrar la liquidacion de un evento finalizado
      [
        'nombre' => 'Verano en la azotea',
        'descripcion' => 'Ciclo de conciertos acusticos al aire libre celebrado durante el mes de julio.',
        'ubicacion' => 'Sala Timple, Calle Perojo 14, Las Palmas de Gran Canaria',
        'fecha' => '2026-07-19 21:30:00',
        'imagen' => 'PENDIENTE',
        'id_usuario' => $sala,
        'id_categoria' => 1,
      ],
    ];

    foreach ($eventos as $evento) {
      $evento['id_evento'] = (string) Str::uuid();
      DB::table('eventos')->insert($evento);
    }
  }
}
