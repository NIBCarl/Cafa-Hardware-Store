<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;
    public $changeType;
    public $quantityChange;

    public function __construct(Product $product, string $changeType, int $quantityChange)
    {
        $this->product = $product->load('category');
        $this->changeType = $changeType;
        $this->quantityChange = $quantityChange;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('inventory');
    }

    public function broadcastAs(): string
    {
        return 'inventory.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'sku' => $this->product->sku,
                'stock_quantity' => $this->product->stock_quantity,
                'low_stock_threshold' => $this->product->low_stock_threshold,
                'is_low_stock' => $this->product->stock_quantity <= $this->product->low_stock_threshold,
            ],
            'change_type' => $this->changeType,
            'quantity_change' => $this->quantityChange,
            'message' => "{$this->product->name} stock updated: {$this->product->stock_quantity} units",
        ];
    }
}
