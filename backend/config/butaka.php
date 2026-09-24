<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Paginación
  |--------------------------------------------------------------------------
  |
  | Número de elementos que devuelve la API en cada página.
  |
  */

  'eventos_por_pagina' => 6,

  // Eventos que se muestran en la banda de destacados del catálogo, y número de
  // organizadores del ranking entre los que se eligen
  'eventos_destacados' => 5,
  'organizadores_destacados' => 3,
  'compras_por_pagina' => 5,
  'facturas_por_pagina' => 5,

  /*
  |--------------------------------------------------------------------------
  | Límite de compra
  |--------------------------------------------------------------------------
  |
  | Número máximo de entradas que un asistente puede adquirir de un mismo
  | evento, para favorecer el acceso del público frente a la compra masiva.
  |
  */

  'max_entradas_por_evento' => 6,

  /*
  |--------------------------------------------------------------------------
  | Fidelización
  |--------------------------------------------------------------------------
  |
  | El asistente gana un punto por cada euro abonado y necesita veinte puntos
  | para descontar un euro, lo que supone un retorno del 5%.
  |
  */

  'puntos_por_euro' => 1,
  'puntos_por_euro_canje' => 20,
  'min_puntos_canje' => 100,
  'max_porcentaje_canje' => 50,

  /*
  |--------------------------------------------------------------------------
  | Facturación
  |--------------------------------------------------------------------------
  |
  | Porcentaje que Butaka retiene de cada venta y tipos de IVA aplicables. El
  | reducido corresponde a los espectáculos culturales en vivo que se facturan
  | al asistente, y el general al servicio de intermediación que se liquida al
  | organizador.
  |
  */

  'comision' => env('BUTAKA_COMISION', 10),
  'iva_entradas' => 10,
  'iva_comision' => 21,

  /*
  |--------------------------------------------------------------------------
  | Frontend
  |--------------------------------------------------------------------------
  |
  | Dirección de la aplicación de Angular. La usan los enlaces de los correos y
  | las URL de retorno de la pasarela de pago.
  |
  */

  'frontend_url' => env('FRONTEND_URL', 'http://localhost:4200'),

];
