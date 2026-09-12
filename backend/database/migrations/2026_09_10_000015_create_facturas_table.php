<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('facturas', function (Blueprint $table) {
      $table->uuid('id_factura')->primary()->default(DB::raw('(UUID())'));

      // Numero correlativo legal, distinto del identificador interno
      $table->string('numero_factura', 20)->unique();

      $table->unsignedInteger('id_tipo_factura');

      // Destinatario de la factura, asistente en las de compra y organizador en las de liquidacion
      $table->uuid('id_usuario');

      $table->date('fecha_emision');
      $table->decimal('base_imponible', 8, 2);
      $table->decimal('iva', 8, 2);
      $table->decimal('total', 8, 2);

      // Solo en las de compra, la compra que origina el comprobante
      $table->uuid('id_compra')->nullable();

      // Solo en las de liquidacion, el evento liquidado con su recaudacion y la comision aplicada
      $table->uuid('id_evento')->nullable();
      $table->decimal('importe_bruto', 8, 2)->nullable();
      $table->unsignedInteger('comision')->nullable();

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_tipo_factura')->references('id_tipo_factura')->on('tipo_facturas')->onDelete('restrict')->onUpdate('cascade');
      $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('restrict')->onUpdate('cascade');
      $table->foreign('id_compra')->references('id_compra')->on('compras')->onDelete('restrict')->onUpdate('cascade');
      $table->foreign('id_evento')->references('id_evento')->on('eventos')->onDelete('restrict')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('facturas');
  }
};
