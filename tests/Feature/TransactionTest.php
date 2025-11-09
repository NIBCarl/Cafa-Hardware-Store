<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $staff;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true
        ]);

        $this->product = Product::factory()->create([
            'price' => 100,
            'stock_quantity' => 20
        ]);
    }

    public function test_can_create_transaction()
    {
        $response = $this->actingAs($this->staff)
            ->postJson('/api/transactions', [
                'customer_phone' => '1234567890',
                'payment_method' => 'cash',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2
                    ]
                ]
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'customer_phone',
                'total_amount',
                'payment_method',
                'status',
                'items' => [
                    '*' => [
                        'product_id',
                        'quantity',
                        'price',
                        'subtotal'
                    ]
                ]
            ]);

        // Check if stock was reduced
        $this->assertEquals(18, $this->product->fresh()->stock_quantity);

        // Check if inventory movement was created
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'type' => 'out'
        ]);
    }

    public function test_cannot_create_transaction_with_insufficient_stock()
    {
        $response = $this->actingAs($this->staff)
            ->postJson('/api/transactions', [
                'payment_method' => 'cash',
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 25 // More than available stock
                    ]
                ]
            ]);

        $response->assertStatus(422);
        
        // Check if stock remained unchanged
        $this->assertEquals(20, $this->product->fresh()->stock_quantity);
    }

    public function test_can_refund_transaction()
    {
        $transaction = Transaction::factory()->create([
            'status' => 'completed',
            'staff_id' => $this->staff->id
        ]);

        $response = $this->actingAs($this->staff)
            ->postJson("/api/transactions/{$transaction->id}/refund", [
                'reason' => 'Customer request'
            ]);

        $response->assertStatus(200);

        $this->assertEquals('refunded', $transaction->fresh()->status);
    }

    public function test_cannot_refund_already_refunded_transaction()
    {
        $transaction = Transaction::factory()->create([
            'status' => 'refunded',
            'staff_id' => $this->staff->id
        ]);

        $response = $this->actingAs($this->staff)
            ->postJson("/api/transactions/{$transaction->id}/refund", [
                'reason' => 'Customer request'
            ]);

        $response->assertStatus(422);
    }
}
