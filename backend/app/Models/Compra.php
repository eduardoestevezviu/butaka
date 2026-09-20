<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Compra extends Model {
  use SoftDeletes;

  protected $table = 'compras';
  protected $primaryKey = 'id_compra';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'id_usuario',
    'id_estado_pago',
    'importe_total',
    'puntos_ganados',
    'puntos_canjeados',
    'fecha_pago',
    'session_id',
  ];

  // El borrado lógico es un detalle interno, no se publica en la API
  protected $hidden = [
    'deleted_at',
  ];

  public function getRouteKeyName(): string {
    return 'id_compra';
  }

  // Se utiliza para generar un UUID automáticamente al crear una nueva compra
  protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
      if (empty($model->id_compra)) {
        $model->id_compra = Str::uuid();
      }
    });
  }

  public function asistente() {
    return $this->belongsTo(Asistente::class, 'id_usuario', 'id_usuario');
  }

  public function estadoPago() {
    return $this->belongsTo(EstadoPago::class, 'id_estado_pago', 'id_estado_pago');
  }

  public function detalleCompras() {
    return $this->hasMany(DetalleCompra::class, 'id_compra', 'id_compra');
  }

  public function factura() {
    return $this->hasOne(Factura::class, 'id_compra', 'id_compra');
  }
}
