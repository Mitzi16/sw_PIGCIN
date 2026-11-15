<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo añade la columna si no existe en la tabla 'calls'
        if (!Schema::hasColumn('calls', 'institution_id')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Solo elimina la columna si existe
        if (Schema::hasColumn('calls', 'institution_id')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->dropForeign(['institution_id']);
                $table->dropColumn('institution_id');
            });
        }
    }
};