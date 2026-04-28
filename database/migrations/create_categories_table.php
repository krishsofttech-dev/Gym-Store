<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LESSON: Categories come BEFORE products in migration order because
     * products will have a FOREIGN KEY referencing categories.
     * Foreign keys require the referenced table to exist first.
     *
     * Categories example: Dumbbells, Resistance Bands, Protein, Apparel
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');           // e.g. "Dumbbells"
            $table->string('slug')->unique(); // e.g. "dumbbells" — used in URLs

            // LESSON: A "slug" is a URL-friendly version of the name.
            // Instead of /category/1, you get /category/dumbbells
            // Much better for SEO and readability.

            $table->text('description')->nullable();
            $table->string('image')->nullable(); // category banner image
            $table->integer('sort_order')->default(0); // for manual ordering in menu

            // Self-referencing for subcategories:
            // e.g. "Cardio Equipment" > "Treadmills" > "Home Treadmills"
            // LESSON: nullable() because top-level categories have no parent
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories') // references same table
                  ->nullOnDelete();           // if parent deleted, set null (not cascade delete)

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};