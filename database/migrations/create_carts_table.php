<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LESSON: The cart has TWO tables — carts and cart_items.
     * This is a "one-to-many" relationship:
     *   One cart  many cart items
     *
     * Why separate tables?
     * Because each item needs its own quantity, price snapshot, etc.
     * You can't store multiple items in a single row — that breaks "First Normal Form" (1NF).
     *
     * ALSO NOTE: We support GUEST carts (session_id for non-logged-in users).
     * When they log in, we MERGE the guest cart into their user cart.
     * This is the AliExpress pattern — shop first, login later.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Either user_id OR session_id — one can be null
            $table->foreignId('user_id')
                  ->nullable()           // null = guest cart
                  ->constrained()
                  ->cascadeOnDelete();   // delete cart when user deleted

            $table->string('session_id')->nullable(); // for guest carts
            // LESSON: session_id is Laravel's session identifier string

            $table->timestamps();

            // Ensure one cart per user (not per session)
            $table->unique('user_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                  ->constrained()
                  ->cascadeOnDelete(); // delete items when cart deleted

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            // LESSON: We snapshot the price at time of adding to cart.
            // If a product price changes, cart keeps the original price.
            // This is correct e-commerce behavior (AliExpress does this too).
            $table->decimal('unit_price', 10, 2);

            // Optional: for future product variants (size, color, etc.)
            $table->json('options')->nullable();
            // e.g. {"color": "black", "size": "XL"}

            $table->timestamps();

            // Prevent duplicate product in same cart
            $table->unique(['cart_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
