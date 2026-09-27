<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('carritos', function (Blueprint $table) {
      $table->uuid('id_carrito')->primary()->default(DB::raw('(UUID())'));

      // Asistente propietario del carrito. Es único: el carrito acompaña al
      // asistente durante toda su vida en la plataforma, y sus líneas entran y
      // salen a medida que añade o compra entradas
      $table->uuid('id_usuario')->unique();

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_usuario')->references('id_usuario')->on('asistentes')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('carritos');
  }
};
