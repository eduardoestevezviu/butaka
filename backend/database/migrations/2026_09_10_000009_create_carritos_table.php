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

      // Asistente propietario del carrito
      $table->uuid('id_usuario');

      // Todo carrito nace activo y pasa a convertido al generarse la compra
      $table->unsignedInteger('id_estado_carrito')->default(1);

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_usuario')->references('id_usuario')->on('asistentes')->onDelete('cascade')->onUpdate('cascade');
      $table->foreign('id_estado_carrito')->references('id_estado_carrito')->on('estado_carritos')->onDelete('restrict')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('carritos');
  }
};
