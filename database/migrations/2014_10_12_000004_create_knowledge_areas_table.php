<?php

use App\Models\KnowledgeArea;
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
        Schema::create('knowledge_areas', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description');
            $table->foreignIdFor(KnowledgeArea::class, 'parent_id')->nullable()
            ->constrained(table: 'knowledge_areas', indexName:'id')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_areas');
    }
};
