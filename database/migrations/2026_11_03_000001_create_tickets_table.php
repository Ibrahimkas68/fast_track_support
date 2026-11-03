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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->enum('status', ['open', 'in_progress', 'resolved']);
            $table->enum('suggested_priority', ['low', 'medium', 'high']);
            $table->string('analysis_reason', 500);
            $table->enum('analysis_source', ['llm', 'local']);
            $table->timestamps();

            $table->index(['created_at', 'id']);
            $table->index(['status', 'created_at', 'id']);
            $table->index(['priority', 'created_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
