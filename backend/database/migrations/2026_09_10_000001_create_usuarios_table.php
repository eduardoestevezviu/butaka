<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('usuarios', function (Blueprint $table) {
      $table->uuid('id_usuario')->primary()->default(DB::raw('(UUID())'));
      $table->string('nombre', 100);
      $table->string('apellidos', 100);
      $table->string('email', 100)->unique();
      $table->string('password', 255);
      $table->string('numero_telefono', 20)->nullable();
      $table->string('calle', 255)->nullable();
      $table->string('vivienda', 50)->nullable();
      $table->string('ciudad', 100)->nullable();
      $table->string('codigo_postal', 5)->nullable();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('usuarios');
  }
};
