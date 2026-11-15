<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_user', function (Blueprint $table) {
            $table->id();
            // Clave foránea para el usuario
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Clave foránea para la convocatoria
            $table->foreignId('call_id')->constrained('calls')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_user');
    }
};
