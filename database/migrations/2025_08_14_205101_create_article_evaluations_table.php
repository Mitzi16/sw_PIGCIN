<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_evaluations', function (Blueprint $table) {;
            $table->id();
            $table->unsignedBigInteger('article_id'); // artículo evaluado
            $table->unsignedBigInteger('rubric_item_id'); // pregunta
            $table->unsignedBigInteger('rubric_answer_id'); // respuesta elegida
            $table->integer('score'); // puntaje de esa respuesta
            $table->unsignedBigInteger('user_id')->nullable(); // evaluador (opcional)
            $table->timestamps();

            // Opcional: claves foráneas
            $table->foreign('rubric_item_id')->references('id')->on('rubric_items')->onDelete('cascade');
            $table->foreign('rubric_answer_id')->references('id')->on('rubric_answers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_evaluations');
    }
};
