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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('prompt');
            $table->decimal('points', 8, 2)->default(1);
            $table->unsignedInteger('order_index')->default(0);
            $table->json('accepted_text_aliases')->nullable();
            $table->decimal('numerical_correct_answer', 14, 4)->nullable();
            $table->decimal('numerical_tolerance', 14, 4)->default(0.01);
            $table->boolean('binary_correct_answer')->nullable();
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index(['quiz_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
