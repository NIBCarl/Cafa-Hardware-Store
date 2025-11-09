<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Power Tools',
                'description' => 'Electric and battery-powered tools for construction and woodworking',
                'is_active' => true,
            ],
            [
                'name' => 'Hand Tools',
                'description' => 'Manual tools including hammers, screwdrivers, wrenches, and more',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing',
                'description' => 'Pipes, fittings, faucets, and plumbing accessories',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical',
                'description' => 'Wiring, switches, outlets, and electrical components',
                'is_active' => true,
            ],
            [
                'name' => 'Paint & Accessories',
                'description' => 'Paints, primers, brushes, rollers, and painting supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Building Materials',
                'description' => 'Lumber, cement, sand, gravel, and construction materials',
                'is_active' => true,
            ],
            [
                'name' => 'Hardware & Fasteners',
                'description' => 'Nuts, bolts, screws, nails, and other fastening hardware',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Equipment',
                'description' => 'Helmets, gloves, goggles, and other safety gear',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}