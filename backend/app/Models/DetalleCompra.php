<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Awobaz\Compoships\Compoships;

class DetalleCompra extends Model {
  use SoftDeletes, Compoships;

  protected $table = 'detalle_compras';
  protected $primaryKey = null;
  public $incrementing = false;

  protected $fillable = [
    'id_compra',
    'id_tipo_entrada',
    'cantidad',
    'precio_unitario',
  ];

  public function compra() {
    return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
  }

  public function tipoEntrada() {
    return $this->belongsTo(TipoEntrada::class, 'id_tipo_entrada', 'id_tipo_entrada');
  }
}
