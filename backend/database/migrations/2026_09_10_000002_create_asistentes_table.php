<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('asistentes', function (Blueprint $table) {
      // La clave primaria es a su vez foranea, al tratarse de un subtipo de usuario
      $table->uuid('id_usuario')->primary();

      // Saldo de puntos de fidelizacion acumulado por el asistente
      $table->unsignedInteger('puntos')->default(0);

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('asistentes');
  }
};
