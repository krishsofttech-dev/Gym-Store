<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Admin User ----
        DB::table('users')->insertOrIgnore([
            'name'              => 'Admin User',
            'email'             => 'admin@gymstore.com',
            'password'          => Hash::make('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ---- Test Customer ----
        DB::table('users')->insertOrIgnore([
            'name'              => 'Test Customer',
            'email'             => 'customer@gymstore.com',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ---- Categories ----
        $categories = [
            ['name' => 'Free Weights',     'slug' => 'free-weights',     'sort_order' => 1],
            ['name' => 'Cardio Equipment', 'slug' => 'cardio-equipment', 'sort_order' => 2],
            ['name' => 'Resistance Bands', 'slug' => 'resistance-bands', 'sort_order' => 3],
            ['name' => 'Gym Apparel',      'slug' => 'gym-apparel',      'sort_order' => 4],
            ['name' => 'Supplements',      'slug' => 'supplements',      'sort_order' => 5],
            ['name' => 'Accessories',      'slug' => 'accessories',      'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            if (DB::table('categories')->where('slug', $cat['slug'])->exists()) continue;
            DB::table('categories')->insert(array_merge($cat, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ---- Products ----
        $this->call(ProductSeeder::class);

        // ---- Sample Coupon ----
        if (! DB::table('coupons')->where('code', 'GYMFIT20')->exists()) {
            DB::table('coupons')->insert([
                'code'          => 'GYMFIT20',
                'type'          => 'percentage',
                'value'         => 20.00,
                'minimum_order' => 5000.00,
                'usage_limit'   => 100,
                'used_count'    => 0,
                'is_active'     => true,
                'expires_at'    => now()->addMonths(3),
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->command->info('✓ Database seeded successfully!');
        $this->command->info('  Admin:    admin@gymstore.com / password');
        $this->command->info('  Customer: customer@gymstore.com / password');
    }
}