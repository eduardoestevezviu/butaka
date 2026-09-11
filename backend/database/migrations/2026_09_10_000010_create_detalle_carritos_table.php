<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('detalle_carritos', function (Blueprint $table) {
      $table->uuid('id_carrito');
      $table->uuid('id_tipo_entrada');
      $table->unsignedInteger('cantidad');

      // Se congela el precio del momento en que se anade la entrada al carrito
      $table->decimal('precio_unitario', 8, 2);

      // La clave primaria es compuesta, sin identificador propio
      $table->primary(['id_carrito', 'id_tipo_entrada']);

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_carrito')->references('id_carrito')->on('carritos')->onDelete('cascade')->onUpdate('cascade');
      $table->foreign('id_tipo_entrada')->references('id_tipo_entrada')->on('tipo_entradas')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('detalle_carritos');
  }
};
