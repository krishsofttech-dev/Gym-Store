<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LESSON: Orders are PERMANENT records. Unlike the cart (temporary),
     * orders must NEVER be deleted. They're financial records.
     *
     * IMPORTANT PATTERN: We store a FULL SNAPSHOT of the order at time of purchase:
     * - Product name, price, quantity — even if the product later changes
     * - Shipping address — even if the user later changes their address
     * - Discount applied — even if the coupon is deleted later
     *
     * This is called "denormalization for historical integrity" — a core
     * principle of e-commerce database design.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Human-readable order number: e.g. "GYM-2024-00142"
            $table->string('order_number')->unique();

            $table->foreignId('user_id')
                  ->constrained()
                  ->restrictOnDelete(); // NEVER delete a user who has orders

            // ---- Financial Totals ----
            $table->decimal('subtotal', 10, 2);      // before discounts/shipping
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);         // final amount charged

            // ---- Status ----
            // LESSON: Use string statuses, not integers — much more readable.
            // pending  confirmed  processing  shipped  delivered  cancelled/refunded
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid'); // unpaid|paid|refunded
            $table->string('payment_method')->nullable();         // stripe|cod|etc.

            // ---- Payment gateway data ----
            $table->string('stripe_payment_intent_id')->nullable();
            // LESSON: We store the Stripe ID so we can look up the payment
            // in the Stripe dashboard and process refunds.

            // ---- Snapshot: Shipping Address ----
            // LESSON: We DON'T use a foreign key to an addresses table.
            // We copy the address directly so it's preserved forever.
            $table->string('shipping_name');
            $table->string('shipping_email');
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postal_code');
            $table->string('shipping_country')->default('LK'); // Sri Lanka default

            // ---- Coupon snapshot ----
            $table->string('coupon_code')->nullable();

            // ---- Notes ----
            $table->text('customer_notes')->nullable(); // customer's special instructions
            $table->text('admin_notes')->nullable();    // internal notes

            // ---- Tracking ----
            $table->string('tracking_number')->nullable();
            $table->string('shipping_carrier')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // LESSON: We use nullable() on product_id because the product
            // might be deleted later. The order item STILL MUST exist
            // (it's a financial record). softDeletes on products helps too.
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            // ---- Full product snapshot at time of purchase ----
            $table->string('product_name');           // copied from product
            $table->string('product_sku')->nullable();
            $table->string('product_image')->nullable();
            $table->decimal('unit_price', 10, 2); // price at time of purchase
            $table->decimal('subtotal', 10, 2);   // unit_price × quantity
            $table->unsignedInteger('quantity');
            $table->json('options')->nullable();          // size, color, etc.

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
