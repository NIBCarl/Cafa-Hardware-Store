<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true
        ]);
    }

    public function test_admin_can_create_product()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'sku' => 'TEST-001',
                'price' => 99.99,
                'stock_quantity' => 10,
                'low_stock_threshold' => 5,
                'category_id' => $category->id
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'sku',
                'price',
                'stock_quantity',
                'category' => [
                    'id',
                    'name'
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001'
        ]);
    }

    public function test_staff_cannot_create_product()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->staff)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'sku' => 'TEST-001',
                'price' => 99.99,
                'stock_quantity' => 10,
                'category_id' => $category->id
            ]);

        $response->assertStatus(403);
    }

    public function test_can_list_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->actingAs($this->staff)
            ->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'price',
                        'stock_quantity'
                    ]
                ],
                'current_page',
                'total'
            ]);
    }

    public function test_can_adjust_stock()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/products/{$product->id}/adjust-stock", [
                'new_quantity' => 15,
                'notes' => 'Stock adjustment test'
            ]);

        $response->assertStatus(200);
        
        $this->assertEquals(15, $product->fresh()->stock_quantity);
        
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'quantity' => 5,
            'type' => 'in',
            'notes' => 'Stock adjustment test'
        ]);
    }

    public function test_cannot_set_negative_stock()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 10
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/products/{$product->id}/adjust-stock", [
                'new_quantity' => -5
            ]);

        $response->assertStatus(422);
    }
}
