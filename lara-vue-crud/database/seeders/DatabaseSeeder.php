<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'customer',
        ]);

        $seller = User::factory()->create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'role' => 'seller',
        ]);

        // Create some sample products
        \App\Models\Product::create([
            'name' => 'Laptop',
            'price' => 999.99,
            'description' => 'High-performance laptop',
            'seller_id' => $seller->id,
        ]);

        \App\Models\Product::create([
            'name' => 'Smartphone',
            'price' => 599.99,
            'description' => 'Latest smartphone model',
            'seller_id' => $seller->id,
        ]);

        \App\Models\Product::create([
            'name' => 'Headphones',
            'price' => 199.99,
            'description' => 'Wireless noise-canceling headphones',
            'seller_id' => $seller->id,
        ]);

        // Seed customers
        $this->call(CustomerSeeder::class);
    }
}
