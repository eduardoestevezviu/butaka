<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TipoEntrada extends Model {
  use SoftDeletes;

  protected $table = 'tipo_entradas';
  protected $primaryKey = 'id_tipo_entrada';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'nombre',
    'precio',
    'oferta',
    'aforo',
    'vendidas',
    'id_evento',
  ];

  // El borrado lógico es un detalle interno, no se publica en la API
  protected $hidden = [
    'deleted_at',
  ];

  public function getRouteKeyName(): string {
    return 'id_tipo_entrada';
  }

  // Precio al que se vende realmente la entrada, con la oferta ya aplicada. Vive
  // en el modelo para que el carrito y la compra calculen siempre de la misma forma
  public function getPrecioEfectivoAttribute(): float {
    if (is_null($this->oferta)) {
      return (float) $this->precio;
    }

    return round($this->precio * (100 - $this->oferta) / 100, 2);
  }

  // Se utiliza para generar un UUID automáticamente al crear un nuevo tipo de entrada
  protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
      if (empty($model->id_tipo_entrada)) {
        $model->id_tipo_entrada = Str::uuid();
      }
    });
  }

  public function evento() {
    return $this->belongsTo(Evento::class, 'id_evento', 'id_evento');
  }

  public function detalleCarritos() {
    return $this->hasMany(DetalleCarrito::class, 'id_tipo_entrada', 'id_tipo_entrada');
  }

  public function detalleCompras() {
    return $this->hasMany(DetalleCompra::class, 'id_tipo_entrada', 'id_tipo_entrada');
  }
}
