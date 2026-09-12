<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organizador extends Model {
  use SoftDeletes;

  protected $table = 'organizadores';
  protected $primaryKey = 'id_usuario';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'id_usuario',
    'nombre_comercial',
    'nif',
    'iban',
  ];

  public function getRouteKeyName(): string {
    return 'id_usuario';
  }

  public function usuario() {
    return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
  }

  public function eventos() {
    return $this->hasMany(Evento::class, 'id_usuario', 'id_usuario');
  }
}
