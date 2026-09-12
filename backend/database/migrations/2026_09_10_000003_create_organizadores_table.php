<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('organizadores', function (Blueprint $table) {
      // La clave primaria es a su vez foranea, al tratarse de un subtipo de usuario
      $table->uuid('id_usuario')->primary();

      // Nombre con el que el organizador aparece publicamente en el catalogo
      $table->string('nombre_comercial', 150)->unique();

      // Datos fiscales necesarios para emitir las liquidaciones al organizador
      $table->string('nif', 9)->unique();
      $table->string('iban', 34);

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('organizadores');
  }
};
