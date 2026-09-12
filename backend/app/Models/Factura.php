<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Factura extends Model {
  use SoftDeletes;

  protected $table = 'facturas';
  protected $primaryKey = 'id_factura';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'numero_factura',
    'id_tipo_factura',
    'id_usuario',
    'fecha_emision',
    'base_imponible',
    'iva',
    'total',
    'id_compra',
    'id_evento',
    'importe_bruto',
    'comision',
  ];

  public function getRouteKeyName(): string {
    return 'id_factura';
  }

  // Se utiliza para generar un UUID automáticamente al crear una nueva factura
  protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
      if (empty($model->id_factura)) {
        $model->id_factura = Str::uuid();
      }
    });
  }

  public function tipoFactura() {
    return $this->belongsTo(TipoFactura::class, 'id_tipo_factura', 'id_tipo_factura');
  }

  public function usuario() {
    return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
  }

  public function compra() {
    return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
  }

  public function evento() {
    return $this->belongsTo(Evento::class, 'id_evento', 'id_evento');
  }
}
