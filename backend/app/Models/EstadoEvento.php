<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoEvento extends Model {
  use SoftDeletes;

  const ESTADO_PUBLICADO = 1;
  const ESTADO_CANCELADO = 2;

  protected $table = 'estado_eventos';
  protected $primaryKey = 'id_estado_evento';
  public $keyType = 'int';
  public $incrementing = true;

  protected $fillable = [
    'estado_evento',
  ];

  public function eventos() {
    return $this->hasMany(Evento::class, 'id_estado_evento', 'id_estado_evento');
  }
}
