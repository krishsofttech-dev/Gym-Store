<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * LESSON: This is a "migration" — think of it as a version-controlled
     * blueprint for your database table. Laravel runs these in order.
     * Each migration has an "up" (create) and "down" (rollback) method.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary key — auto-incrementing integer ID
            $table->id();

            // Basic profile info
            $table->string('name');
            $table->string('email')->unique(); // unique = no duplicate emails
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role: 'customer' or 'admin'
            // LESSON: We use an enum-like string with a default
            $table->string('role')->default('customer');

            // Profile extras
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable(); // path to profile image

            // Remember me token for "stay logged in"
            $table->rememberToken();

            // Laravel auto-manages created_at and updated_at
            $table->timestamps();
        });

        // Password reset tokens table (required by Laravel Auth)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Login sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};