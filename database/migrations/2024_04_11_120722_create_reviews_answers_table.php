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
        Schema::create('reviews_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('review_id')->constrained('reviews');
            $table->foreignId('author_id')->constrained('users');
            $table->text('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews_answers');
    }
};
