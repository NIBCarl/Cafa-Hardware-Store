<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product->load('category');
    }

    public function broadcastOn(): Channel
    {
        return new Channel('alerts');
    }

    public function broadcastAs(): string
    {
        return 'stock.low';
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
                'category' => $this->product->category->name ?? 'Uncategorized',
            ],
            'severity' => $this->product->stock_quantity === 0 ? 'critical' : 'warning',
            'message' => "⚠️ Low stock alert: {$this->product->name} ({$this->product->stock_quantity} units remaining)",
        ];
    }
}
