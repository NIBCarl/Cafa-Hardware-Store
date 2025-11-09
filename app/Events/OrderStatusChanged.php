<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $oldStatus;
    public $newStatus;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order->load(['customer', 'items.product']);
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
            new Channel('customer.' . $this->order->customer_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->newStatus,
                'total_amount' => $this->order->total_amount,
                'customer_name' => $this->order->customer->name ?? 'Guest',
            ],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Order {$this->order->order_number} status changed to {$this->newStatus}",
        ];
    }
}
