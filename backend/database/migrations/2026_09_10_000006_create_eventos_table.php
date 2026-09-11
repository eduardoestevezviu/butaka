<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('eventos', function (Blueprint $table) {
      $table->uuid('id_evento')->primary()->default(DB::raw('(UUID())'));
      $table->string('nombre', 150);
      $table->text('descripcion')->nullable();
      $table->string('ubicacion', 255);

      // Se almacena la fecha con la hora de comienzo del evento
      $table->dateTime('fecha');
      // Si el organizador no aporta imagen se emplea la de por defecto
      $table->string('imagen', 255)->default('img/evento-por-defecto.jpg');

      // Organizador propietario del evento
      $table->uuid('id_usuario');

      $table->integer('id_categoria');

      // Todo evento nace publicado y solo puede pasar a cancelado
      $table->integer('id_estado_evento')->default(1);

      $table->timestamps();
      $table->softDeletes();

      $table->foreign('id_usuario')->references('id_usuario')->on('organizadores')->onDelete('cascade')->onUpdate('cascade');
      $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('restrict')->onUpdate('cascade');
      $table->foreign('id_estado_evento')->references('id_estado_evento')->on('estado_eventos')->onDelete('restrict')->onUpdate('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('eventos');
  }
};
