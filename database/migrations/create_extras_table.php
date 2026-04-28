<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Wishlists ----
        // LESSON: Simple pivot table — user saves products for later.
        // This IS a true pivot table (no extra data), so it's lean.
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'product_id']); // Can't wishlist same item twice
        });

        // ---- Saved Addresses ----
        // LESSON: Users can save multiple shipping addresses (home, office, etc.)
        // During checkout, they pick one — and we copy it to the order.
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('label')->nullable(); // "Home", "Office", "Gym"
            $table->string('name');              // recipient name
            $table->string('phone')->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('country')->default('LK');
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });

        // ---- Coupons ----
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();   // e.g. "GYMFIT20"
            $table->string('type');             // 'percentage' or 'fixed'
            $table->decimal('value', 10, 2); // 20 = 20% off, or Rs.500 off
            $table->decimal('minimum_order', 10, 2)->default(0);
            $table->decimal('maximum_discount', 10, 2)->nullable();

            $table->unsignedInteger('usage_limit')->nullable(); // null = unlimited
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('wishlists');
    }
};
