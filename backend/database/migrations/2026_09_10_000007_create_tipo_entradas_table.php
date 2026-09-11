<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('tipo_entradas', function (Blueprint $table) {
      $table->uuid('id_tipo_entrada')->primary()->default(DB::raw('(UUID())'));
      $table->string('nombre', 100);
      $table->decimal('precio', 8, 2);

      // Porcentaje de descuento aplicable sobre el precio
      $table->unsignedInteger('oferta')->nullable();

      // El aforo es el total definido por el organizador y no varia con las ventas
      $table->unsignedInteger('aforo');

      // Contador de entradas vendidas, las disponibles son la diferencia con el aforo
      $table->unsignedInteger('vendidas')->default(0);

      $table->uuid('id_evento');

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_evento')->references('id_evento')->on('eventos')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('tipo_entradas');
  }
};
