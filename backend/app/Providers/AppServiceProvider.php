<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider {
  /**
   * Register any application services.
   */
  public function register(): void {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void {
    // Los scopes se corresponden uno a uno con los roles de Spatie. Un mismo
    // token puede llevar los dos, ya que un organizador también compra entradas
    // Un token de acceso robado sirve durante toda su vida, por lo que se acorta
    // el año que trae Passport por defecto a un plazo razonable
    Passport::tokensExpireIn(now()->addDays(7));

    Passport::tokensCan([
      'asistente' => 'Acceso de asistente',
      'organizador' => 'Acceso de organizador',
    ]);
  }
}
