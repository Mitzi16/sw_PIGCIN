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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('type');
            $table->text('abstract');
            $table->text('key_works');
            $table->text('comment')->nullable();
            $table->foreignId('postulant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('editor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('knowledge_area_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('article_status_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('call_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_voucher_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
