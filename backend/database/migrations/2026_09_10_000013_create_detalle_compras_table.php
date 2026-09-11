<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('detalle_compras', function (Blueprint $table) {
      $table->uuid('id_compra');
      $table->uuid('id_tipo_entrada');
      $table->unsignedInteger('cantidad');

      // Precio aplicado en el momento de la compra, no varia aunque cambie la tarifa del evento
      $table->decimal('precio_unitario', 8, 2);

      // La clave primaria es compuesta, sin identificador propio
      $table->primary(['id_compra', 'id_tipo_entrada']);

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_compra')->references('id_compra')->on('compras')->onDelete('cascade')->onUpdate('cascade');
      $table->foreign('id_tipo_entrada')->references('id_tipo_entrada')->on('tipo_entradas')->onDelete('restrict')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('detalle_compras');
  }
};
