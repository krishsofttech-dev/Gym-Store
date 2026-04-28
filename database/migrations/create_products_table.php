<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LESSON: This is the most important table — the heart of the store.
     * Notice how much thought goes into data types:
     *   - decimal for prices (never float — floating point errors!)
     *   - string for SKU (Stock Keeping Unit)
     *   - JSON for flexible attributes like color options or size variants
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // ---- Core Info ----
            $table->string('name');
            $table->string('slug')->unique();   // URL-friendly name
            $table->text('description');        // Full product description
            $table->text('short_description')->nullable(); // For product cards

            // ---- Pricing ----
            // LESSON: ALWAYS use decimal for money, never float!
            // float(10.00) might become 9.999999999 due to floating point math.
            // decimal stores EXACT values. 8 digits total, 2 decimal places.
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            // compare_price = original price before discount (shows strikethrough)

            $table->decimal('cost_price', 10, 2)->nullable();
            // cost_price = what YOU paid wholesale (private, for profit calculations)

            // ---- Inventory ----
            $table->string('sku')->unique()->nullable();  // e.g. "DUMB-20KG-BLK"
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('track_quantity')->default(true);
            $table->boolean('in_stock')->default(true);

            // ---- Organisation ----
            // LESSON: constrained() auto-detects foreign key from column name
            // It links category_id  categories.id
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            // restrictOnDelete = can't delete a category that has products

            $table->string('brand')->nullable();

            // ---- Media ----
            $table->string('thumbnail')->nullable();   // main product image
            $table->json('images')->nullable();
            // LESSON: JSON columns let us store arrays in SQLite.
            // images = ["img1.jpg", "img2.jpg", "img3.jpg"]
            // Accessed in Laravel as: $product->images (auto-decoded to array)

            // ---- Physical specs (for shipping) ----
            $table->decimal('weight', 8, 2)->nullable(); // in kg
            $table->string('dimensions')->nullable(); // e.g. "30x20x15 cm"

            // ---- SEO ----
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // ---- Status & Visibility ----
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // show on homepage
            $table->boolean('is_new')->default(false);      // "New Arrival" badge

            // ---- Stats (cached for performance) ----
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sales_count')->default(0);

            $table->timestamps();
            $table->softDeletes(); // LESSON: adds deleted_at column.
            // Soft delete = record stays in DB but is "hidden".
            // $product->delete() sets deleted_at = now().
            // Perfect for order history — you need old product data!
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
