<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoCarrito extends Model {
  use SoftDeletes;

  const ESTADO_ACTIVO = 1;
  const ESTADO_CONVERTIDO = 2;

  protected $table = 'estado_carritos';
  protected $primaryKey = 'id_estado_carrito';
  public $keyType = 'int';
  public $incrementing = true;

  protected $fillable = [
    'estado_carrito',
  ];

  public function carritos() {
    return $this->hasMany(Carrito::class, 'id_estado_carrito', 'id_estado_carrito');
  }
}
