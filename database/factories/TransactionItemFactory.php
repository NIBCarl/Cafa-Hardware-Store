<?php

namespace Database\Factories;

use App\Models\TransactionItem;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(1, 5);

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'subtotal' => $product->price * $quantity,
        ];
    }
}
