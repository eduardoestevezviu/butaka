<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleCarrito extends Model {
  use SoftDeletes;

  protected $table = 'detalle_carritos';
  protected $primaryKey = null;
  public $incrementing = false;

  protected $fillable = [
    'id_carrito',
    'id_tipo_entrada',
    'cantidad',
    'precio_unitario',
  ];

  public function carrito() {
    return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
  }

  public function tipoEntrada() {
    return $this->belongsTo(TipoEntrada::class, 'id_tipo_entrada', 'id_tipo_entrada');
  }
}
