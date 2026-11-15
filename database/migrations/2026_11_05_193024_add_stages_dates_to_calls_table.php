<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            // Etapa 1: Publicación
            $table->date('publication_start')->nullable();
            $table->date('publication_end')->nullable();
            
            // Etapa 2: Registro
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            
            // Etapa 3: Postulación
            $table->date('application_start')->nullable();
            $table->date('application_end')->nullable();
            
            // Etapa 4: Evaluación
            $table->date('evaluation_start')->nullable();
            $table->date('evaluation_end')->nullable();
            
            // Etapa 5: Resultados
            $table->date('results_start')->nullable();
            $table->date('results_end')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn([
                'publication_start', 'publication_end',
                'registration_start', 'registration_end',
                'application_start', 'application_end',
                'evaluation_start', 'evaluation_end',
                'results_start', 'results_end',
            ]);
        });
    }
};