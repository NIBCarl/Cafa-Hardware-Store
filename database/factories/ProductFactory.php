<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'sku' => fake()->unique()->regexify('[A-Z]{2}-[0-9]{4}'),
            'barcode' => fake()->unique()->ean13(),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'cost' => fake()->randomFloat(2, 5, 800),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => fake()->numberBetween(5, 20),
            'category_id' => Category::factory(),
            'is_active' => true,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 3,
            'low_stock_threshold' => 5,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
