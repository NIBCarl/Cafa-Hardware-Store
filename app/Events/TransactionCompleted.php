<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['items.product', 'staff']);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('dashboard');
    }

    public function broadcastAs(): string
    {
        return 'transaction.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'transaction' => [
                'id' => $this->transaction->id,
                'total_amount' => $this->transaction->total_amount,
                'payment_method' => $this->transaction->payment_method,
                'staff_name' => $this->transaction->staff->name ?? 'Unknown',
                'items_count' => $this->transaction->items->count(),
                'created_at' => $this->transaction->created_at->toISOString(),
            ],
            'message' => "New transaction ₱" . number_format($this->transaction->total_amount, 2),
        ];
    }
}
