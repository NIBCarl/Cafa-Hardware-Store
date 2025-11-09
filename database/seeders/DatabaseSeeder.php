<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class,        // Commented out - add products manually via UI
            // TransactionSeeder::class,    // Commented out - test with real transactions
        ]);
    }
}