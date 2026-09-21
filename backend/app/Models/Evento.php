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

  // El borrado lógico es un detalle interno, no se publica en la API
  protected $hidden = [
    'deleted_at',
  ];

  // La dirección completa de la imagen viaja siempre en la respuesta, para que el
  // frontend no tenga que saber dónde se alojan las imágenes
  protected $appends = [
    'imagen_url',
  ];

  public function getRouteKeyName(): string {
    return 'id_evento';
  }

  // Las imágenes propias se guardan como ruta relativa y se sirven desde public/
  // del backend, mientras que las que aporta el organizador ya son una URL
  public function getImagenUrlAttribute(): ?string {
    if ($this->imagen === null) {
      return null;
    }

    if (str_starts_with($this->imagen, 'http')) {
      return $this->imagen;
    }

    return asset($this->imagen);
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
