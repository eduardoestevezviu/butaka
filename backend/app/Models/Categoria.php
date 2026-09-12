<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model {
  use SoftDeletes;

  const MUSICA = 1;
  const TEATRO = 2;
  const DANZA = 3;
  const EXPOSICIONES = 4;
  const CURSOS = 5;
  const FAMILIAR = 6;
  const OTROS = 7;

  protected $table = 'categorias';
  protected $primaryKey = 'id_categoria';
  public $keyType = 'int';
  public $incrementing = true;

  protected $fillable = [
    'categoria',
  ];

  public function eventos() {
    return $this->hasMany(Evento::class, 'id_categoria', 'id_categoria');
  }
}
