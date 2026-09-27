<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Carrito extends Model {
  use SoftDeletes;

  protected $table = 'carritos';
  protected $primaryKey = 'id_carrito';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'id_usuario',
  ];

  // El borrado lógico es un detalle interno, no se publica en la API
  protected $hidden = [
    'deleted_at',
  ];

  public function getRouteKeyName(): string {
    return 'id_carrito';
  }

  // Se utiliza para generar un UUID automáticamente al crear un nuevo carrito
  protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
      if (empty($model->id_carrito)) {
        $model->id_carrito = Str::uuid();
      }
    });
  }

  public function asistente() {
    return $this->belongsTo(Asistente::class, 'id_usuario', 'id_usuario');
  }


  public function detalleCarritos() {
    return $this->hasMany(DetalleCarrito::class, 'id_carrito', 'id_carrito');
  }
}
