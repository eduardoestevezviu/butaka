<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable implements OAuthenticatable {
  use HasApiTokens, HasRoles, SoftDeletes;

  protected $table = 'usuarios';
  protected $primaryKey = 'id_usuario';
  public $keyType = 'string';
  public $incrementing = false;

  protected $fillable = [
    'nombre',
    'apellidos',
    'email',
    'password',
    'numero_telefono',
    'calle',
    'vivienda',
    'ciudad',
    'codigo_postal',
  ];

  protected $hidden = [
    'password',
  ];

  public function getRouteKeyName(): string {
    return 'id_usuario';
  }

  // Se utiliza para generar un UUID automáticamente al crear un nuevo usuario
  protected static function boot() {
    parent::boot();
    static::creating(function ($model) {
      if (empty($model->id_usuario)) {
        $model->id_usuario = Str::uuid();
      }
    });
  }

  public function asistente() {
    return $this->hasOne(Asistente::class, 'id_usuario', 'id_usuario');
  }

  public function organizador() {
    return $this->hasOne(Organizador::class, 'id_usuario', 'id_usuario');
  }

  public function facturas() {
    return $this->hasMany(Factura::class, 'id_usuario', 'id_usuario');
  }
}
