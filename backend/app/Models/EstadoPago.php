<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoPago extends Model {
  use SoftDeletes;

  const ESTADO_PENDIENTE = 1;
  const ESTADO_COMPLETADO = 2;
  const ESTADO_REEMBOLSADO = 3;

  protected $table = 'estado_pagos';
  protected $primaryKey = 'id_estado_pago';
  public $keyType = 'int';
  public $incrementing = true;

  protected $fillable = [
    'estado_pago',
  ];

  public function compras() {
    return $this->hasMany(Compra::class, 'id_estado_pago', 'id_estado_pago');
  }
}
