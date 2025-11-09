<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'customer_phone' => fake()->phoneNumber(),
            'total_amount' => fake()->randomFloat(2, 100, 5000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'digital_wallet']),
            'staff_id' => User::factory(),
            'status' => 'completed',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
        ]);
    }
}
