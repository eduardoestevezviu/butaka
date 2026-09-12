<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('estado_eventos', function (Blueprint $table) {
      $table->increments('id_estado_evento');
      $table->enum('estado_evento', ['Publicado', 'Cancelado'])->unique();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('estado_eventos');
  }
};
