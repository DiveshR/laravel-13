<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'iPhone 15 Pro',
                'Galaxy S25 Ultra',
                'MacBook Pro 16',
                'AirPods Max',
                'Sony WH-1000XM6',
                'Dell XPS 13',
                'Logitech MX Master 4',
                'iPad Pro 13',
                'PlayStation 6',
                'Canon EOS R8',
            ]).' '.fake()->numberBetween(1, 5000),
            'description' => fake()->realText(120),
            'user_id' => 1,
        ];
    }
}
