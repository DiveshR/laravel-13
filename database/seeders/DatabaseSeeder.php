<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        User::factory()->admin()->create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin@123456'),
        ]);

        foreach (range(1, 10) as $batch) {
            User::factory()->count(1000)->create();
        }

        $userIds = User::query()->where('role', 'user')->pluck('id')->all();

        foreach (range(1, 50) as $batch) {
            $timestamp = now()->toDateTimeString();
            $products = Product::factory()->count(1000)->make()->map(function ($product) use ($timestamp, $userIds) {
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'user_id' => $userIds[array_rand($userIds)],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            })->all();

            Product::query()->insert($products);
        }
    }
}
