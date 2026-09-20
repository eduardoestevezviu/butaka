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
    // Butaka emite tokens de acceso personal con createToken(), que tienen su
    // propia caducidad. tokensExpireIn() solo afecta a los flujos OAuth2 que
    // aquí no se usan, de modo que no bastaría con ese
    Passport::personalAccessTokensExpireIn(now()->addDays(7));

    // Los scopes se corresponden uno a uno con los roles de Spatie. Un mismo
    // token puede llevar los dos, ya que un organizador también compra entradas
    Passport::tokensCan([
      'asistente' => 'Acceso de asistente',
      'organizador' => 'Acceso de organizador',
    ]);
  }
}
