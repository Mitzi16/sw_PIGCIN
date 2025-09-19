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
        Schema::table('article_evaluations', function (Blueprint $table) {
            $table->decimal('article_score', 8, 2)->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_evaluations', function (Blueprint $table) {
            $table->dropColumn('article_score');
        });
    }
};
