<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('categorias', function (Blueprint $table) {
      $table->increments('id_categoria');
      $table->enum('categoria', ['Música', 'Teatro', 'Danza', 'Exposiciones', 'Cursos y talleres', 'Familiar', 'Otros'])->unique();
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('categorias');
  }
};
