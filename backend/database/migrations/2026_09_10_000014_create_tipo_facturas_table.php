<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('tipo_facturas', function (Blueprint $table) {
      $table->integer('id_tipo_factura')->primary();
      $table->enum('tipo_factura', ['Compra', 'Liquidación'])->unique();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('tipo_facturas');
  }
};
