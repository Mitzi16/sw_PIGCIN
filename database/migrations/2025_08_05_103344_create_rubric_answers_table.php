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
        Schema::create('rubric_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_item_id')->constrained()->onDelete('cascade');
            $table->string('text');     // Texto visible de la respuesta
            $table->integer('score');   // Valor numérico asignado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_answers');
    }
};
