<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Evento extends Model {
  use SoftDeletes;

  protected $table = 'eventos';
  protected $primaryKey = 'id_evento';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'nombre',
    'descripcion',
    'ubicacion',
    'fecha',
    'imagen',
    'id_usuario',
    'id_categoria',
    'id_estado_evento',
  ];

  public function getRouteKeyName(): string {
    return 'id_evento';
  }

  // Se utiliza para generar un UUID automáticamente al crear un nuevo evento
  protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
      if (empty($model->id_evento)) {
        $model->id_evento = Str::uuid();
      }
    });
  }

  public function organizador() {
    return $this->belongsTo(Organizador::class, 'id_usuario', 'id_usuario');
  }

  public function categoria() {
    return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
  }

  public function estadoEvento() {
    return $this->belongsTo(EstadoEvento::class, 'id_estado_evento', 'id_estado_evento');
  }

  public function tipoEntradas() {
    return $this->hasMany(TipoEntrada::class, 'id_evento', 'id_evento');
  }

  public function factura() {
    return $this->hasOne(Factura::class, 'id_evento', 'id_evento');
  }
}
