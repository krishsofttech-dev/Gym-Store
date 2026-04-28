<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LESSON: Reviews link USERS to PRODUCTS.
     * This is a many-to-many with extra data — a classic "pivot table with attributes".
     * But since reviews have lots of data (title, body, rating, status),
     * we create a full table rather than a simple pivot.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // LESSON: tinyInteger is perfect for ratings (1-5).
            // Uses only 1 byte vs 4 bytes for regular integer. Small win, good habit.
            $table->tinyInteger('rating'); // 1 to 5

            $table->string('title')->nullable();     // "Great dumbbells!"
            $table->text('body')->nullable();         // full review text
            $table->json('images')->nullable();       // reviewer can upload photos

            // Moderation status: pending | approved | rejected
            $table->string('status')->default('pending');
            // LESSON: Never auto-publish reviews — spam protection!

            $table->boolean('verified_purchase')->default(false);
            // LESSON: We can check if user_id has an order with product_id.
            // Verified = more trustworthy review.

            $table->unsignedInteger('helpful_count')->default(0);
            // Users can upvote reviews as "helpful" (like Amazon)

            $table->timestamps();

            // One review per product per user
            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};