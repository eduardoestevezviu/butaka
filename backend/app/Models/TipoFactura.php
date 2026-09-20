<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoFactura extends Model {
  use SoftDeletes;

  const TIPO_COMPRA = 1;
  const TIPO_LIQUIDACION = 2;

  protected $table = 'tipo_facturas';
  protected $primaryKey = 'id_tipo_factura';
  public $keyType = 'int';
  public $incrementing = true;

  protected $fillable = [
    'tipo_factura',
  ];

  // El borrado lógico es un detalle interno, no se publica en la API
  protected $hidden = [
    'deleted_at',
  ];

  public function facturas() {
    return $this->hasMany(Factura::class, 'id_tipo_factura', 'id_tipo_factura');
  }
}
