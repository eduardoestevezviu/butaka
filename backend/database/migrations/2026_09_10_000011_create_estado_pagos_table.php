<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('estado_pagos', function (Blueprint $table) {
      $table->integer('id_estado_pago')->primary();
      $table->enum('estado_pago', ['Pendiente', 'Completado', 'Reembolsado'])->unique();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('estado_pagos');
  }
};
