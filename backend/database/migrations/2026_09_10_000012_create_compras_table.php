<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('compras', function (Blueprint $table) {
      $table->uuid('id_compra')->primary()->default(DB::raw('(UUID())'));

      // Asistente que realiza la compra
      $table->uuid('id_usuario');

      // Toda compra nace pendiente de pago
      $table->unsignedInteger('id_estado_pago')->default(1);

      // Importe realmente abonado, ya descontado el canje de puntos
      $table->decimal('importe_total', 8, 2);

      // Puntos concedidos por esta compra y puntos gastados en ella
      $table->unsignedInteger('puntos_ganados')->default(0);
      $table->unsignedInteger('puntos_canjeados')->default(0);

      $table->timestamp('fecha_pago')->nullable();

      // Identificador de la sesion de pago de Stripe, necesario para verificar y reembolsar
      $table->string('session_id')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_usuario')->references('id_usuario')->on('asistentes')->onDelete('restrict')->onUpdate('cascade');
      $table->foreign('id_estado_pago')->references('id_estado_pago')->on('estado_pagos')->onDelete('restrict')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('compras');
  }
};
