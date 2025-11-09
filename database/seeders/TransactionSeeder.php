<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('is_active', true)->get();
        $staff = User::where('role', 'admin')->first();

        if (!$staff || $products->isEmpty()) {
            $this->command->warn('No products or staff found. Skipping transaction seeding.');
            return;
        }

        // Generate transactions for the past 90 days
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(90);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Random number of transactions per day (0-10)
            $transactionsCount = rand(0, 10);

            for ($i = 0; $i < $transactionsCount; $i++) {
                // Random time during the day
                $transactionTime = $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));

                $transaction = Transaction::create([
                    'staff_id' => $staff->id,
                    'total_amount' => 0, // Will be calculated
                    'payment_method' => collect(['cash', 'digital_wallet', 'card'])->random(),
                    'status' => rand(1, 100) <= 95 ? 'completed' : 'refunded', // 95% completed
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);

                // Add 1-5 random items to the transaction
                $itemsCount = rand(1, 5);
                $totalAmount = 0;

                for ($j = 0; $j < $itemsCount; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $price = $product->price;
                    $subtotal = $price * $quantity;
                    $totalAmount += $subtotal;

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Update transaction total
                $transaction->update(['total_amount' => $totalAmount]);
            }
        }

        $this->command->info('Transactions seeded successfully!');
    }
}
