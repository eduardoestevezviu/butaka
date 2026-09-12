<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asistente extends Model {
  use SoftDeletes;

  protected $table = 'asistentes';
  protected $primaryKey = 'id_usuario';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'id_usuario',
    'puntos',
  ];

  public function getRouteKeyName(): string {
    return 'id_usuario';
  }

  public function usuario() {
    return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
  }

  public function carritos() {
    return $this->hasMany(Carrito::class, 'id_usuario', 'id_usuario');
  }

  public function compras() {
    return $this->hasMany(Compra::class, 'id_usuario', 'id_usuario');
  }
}
