<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $powerToolsCategory = Category::where('name', 'Power Tools')->first();
        $handToolsCategory = Category::where('name', 'Hand Tools')->first();
        $plumbingCategory = Category::where('name', 'Plumbing')->first();
        $electricalCategory = Category::where('name', 'Electrical')->first();
        $paintCategory = Category::where('name', 'Paint & Accessories')->first();
        $buildingCategory = Category::where('name', 'Building Materials')->first();
        $hardwareCategory = Category::where('name', 'Hardware & Fasteners')->first();
        $safetyCategory = Category::where('name', 'Safety Equipment')->first();

        $products = [
            // Power Tools
            ['name' => 'Makita Drill Set', 'sku' => 'PT001', 'barcode' => '8885009301234', 'description' => '18V cordless drill with battery and charger', 'price' => 4500.00, 'cost' => 3200.00, 'stock_quantity' => 15, 'low_stock_threshold' => 5, 'category_id' => $powerToolsCategory->id],
            ['name' => 'Angle Grinder 4"', 'sku' => 'PT002', 'barcode' => '8885009301235', 'description' => '750W angle grinder with safety guard', 'price' => 1800.00, 'cost' => 1250.00, 'stock_quantity' => 20, 'low_stock_threshold' => 5, 'category_id' => $powerToolsCategory->id],
            ['name' => 'Circular Saw 7.25"', 'sku' => 'PT003', 'barcode' => '8885009301236', 'description' => '1400W circular saw with laser guide', 'price' => 3200.00, 'cost' => 2300.00, 'stock_quantity' => 12, 'low_stock_threshold' => 4, 'category_id' => $powerToolsCategory->id],
            
            // Hand Tools
            ['name' => 'Hammer Claw 16oz', 'sku' => 'HT001', 'barcode' => '8885009302234', 'description' => 'Steel claw hammer with fiberglass handle', 'price' => 350.00, 'cost' => 220.00, 'stock_quantity' => 45, 'low_stock_threshold' => 10, 'category_id' => $handToolsCategory->id],
            ['name' => 'Screwdriver Set 6pcs', 'sku' => 'HT002', 'barcode' => '8885009302235', 'description' => 'Phillips and flathead screwdriver set', 'price' => 280.00, 'cost' => 180.00, 'stock_quantity' => 60, 'low_stock_threshold' => 15, 'category_id' => $handToolsCategory->id],
            ['name' => 'Adjustable Wrench 10"', 'sku' => 'HT003', 'barcode' => '8885009302236', 'description' => 'Chrome plated adjustable wrench', 'price' => 420.00, 'cost' => 280.00, 'stock_quantity' => 35, 'low_stock_threshold' => 10, 'category_id' => $handToolsCategory->id],
            ['name' => 'Tape Measure 5m', 'sku' => 'HT004', 'barcode' => '8885009302237', 'description' => '5 meter retractable tape measure', 'price' => 180.00, 'cost' => 110.00, 'stock_quantity' => 50, 'low_stock_threshold' => 15, 'category_id' => $handToolsCategory->id],
            
            // Plumbing
            ['name' => 'PVC Pipe 1/2" x 10ft', 'sku' => 'PL001', 'barcode' => '8885009303234', 'description' => 'Schedule 40 PVC pipe', 'price' => 85.00, 'cost' => 55.00, 'stock_quantity' => 100, 'low_stock_threshold' => 20, 'category_id' => $plumbingCategory->id],
            ['name' => 'Faucet Kitchen Single', 'sku' => 'PL002', 'barcode' => '8885009303235', 'description' => 'Chrome finish kitchen faucet', 'price' => 1250.00, 'cost' => 850.00, 'stock_quantity' => 18, 'low_stock_threshold' => 5, 'category_id' => $plumbingCategory->id],
            ['name' => 'PVC Elbow 1/2"', 'sku' => 'PL003', 'barcode' => '8885009303236', 'description' => '90-degree PVC elbow fitting', 'price' => 12.00, 'cost' => 7.00, 'stock_quantity' => 200, 'low_stock_threshold' => 50, 'category_id' => $plumbingCategory->id],
            
            // Electrical
            ['name' => 'Extension Cord 10m', 'sku' => 'EL001', 'barcode' => '8885009304234', 'description' => '3-outlet heavy duty extension cord', 'price' => 650.00, 'cost' => 420.00, 'stock_quantity' => 25, 'low_stock_threshold' => 8, 'category_id' => $electricalCategory->id],
            ['name' => 'Light Switch 2-Gang', 'sku' => 'EL002', 'barcode' => '8885009304235', 'description' => 'Double gang light switch', 'price' => 85.00, 'cost' => 52.00, 'stock_quantity' => 80, 'low_stock_threshold' => 20, 'category_id' => $electricalCategory->id],
            ['name' => 'LED Bulb 9W', 'sku' => 'EL003', 'barcode' => '8885009304236', 'description' => '9W LED bulb cool white', 'price' => 120.00, 'cost' => 75.00, 'stock_quantity' => 150, 'low_stock_threshold' => 30, 'category_id' => $electricalCategory->id],
            
            // Paint & Accessories
            ['name' => 'Latex Paint White 4L', 'sku' => 'PA001', 'barcode' => '8885009305234', 'description' => 'Interior latex paint white', 'price' => 850.00, 'cost' => 580.00, 'stock_quantity' => 40, 'low_stock_threshold' => 10, 'category_id' => $paintCategory->id],
            ['name' => 'Paint Brush 2"', 'sku' => 'PA002', 'barcode' => '8885009305235', 'description' => 'Synthetic bristle paint brush', 'price' => 95.00, 'cost' => 60.00, 'stock_quantity' => 75, 'low_stock_threshold' => 20, 'category_id' => $paintCategory->id],
            ['name' => 'Paint Roller 9"', 'sku' => 'PA003', 'barcode' => '8885009305236', 'description' => '9-inch paint roller with handle', 'price' => 180.00, 'cost' => 115.00, 'stock_quantity' => 55, 'low_stock_threshold' => 15, 'category_id' => $paintCategory->id],
            
            // Building Materials
            ['name' => 'Cement Portland 40kg', 'sku' => 'BM001', 'barcode' => '8885009306234', 'description' => 'Type 1 Portland cement', 'price' => 285.00, 'cost' => 210.00, 'stock_quantity' => 120, 'low_stock_threshold' => 30, 'category_id' => $buildingCategory->id],
            ['name' => 'Plywood 4x8 1/4"', 'sku' => 'BM002', 'barcode' => '8885009306235', 'description' => 'Marine grade plywood sheet', 'price' => 780.00, 'cost' => 580.00, 'stock_quantity' => 35, 'low_stock_threshold' => 10, 'category_id' => $buildingCategory->id],
            
            // Hardware & Fasteners
            ['name' => 'Nails 2" (1kg)', 'sku' => 'HF001', 'barcode' => '8885009307234', 'description' => 'Common wire nails 1kg pack', 'price' => 85.00, 'cost' => 55.00, 'stock_quantity' => 95, 'low_stock_threshold' => 25, 'category_id' => $hardwareCategory->id],
            ['name' => 'Screws Wood 1.5" (100pcs)', 'sku' => 'HF002', 'barcode' => '8885009307235', 'description' => 'Phillips head wood screws', 'price' => 65.00, 'cost' => 42.00, 'stock_quantity' => 110, 'low_stock_threshold' => 30, 'category_id' => $hardwareCategory->id],
            ['name' => 'Bolt & Nut Set M8', 'sku' => 'HF003', 'barcode' => '8885009307236', 'description' => 'M8 bolt and nut set (50pcs)', 'price' => 120.00, 'cost' => 78.00, 'stock_quantity' => 80, 'low_stock_threshold' => 20, 'category_id' => $hardwareCategory->id],
            
            // Safety Equipment
            ['name' => 'Safety Helmet', 'sku' => 'SE001', 'barcode' => '8885009308234', 'description' => 'Industrial safety helmet', 'price' => 380.00, 'cost' => 250.00, 'stock_quantity' => 30, 'low_stock_threshold' => 8, 'category_id' => $safetyCategory->id],
            ['name' => 'Safety Goggles', 'sku' => 'SE002', 'barcode' => '8885009308235', 'description' => 'Clear lens safety goggles', 'price' => 150.00, 'cost' => 95.00, 'stock_quantity' => 45, 'low_stock_threshold' => 12, 'category_id' => $safetyCategory->id],
            ['name' => 'Work Gloves Pair', 'sku' => 'SE003', 'barcode' => '8885009308236', 'description' => 'Leather palm work gloves', 'price' => 220.00, 'cost' => 145.00, 'stock_quantity' => 60, 'low_stock_threshold' => 15, 'category_id' => $safetyCategory->id],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}