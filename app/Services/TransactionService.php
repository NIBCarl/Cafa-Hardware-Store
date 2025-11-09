<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Order;
use App\Events\TransactionCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    protected $inventoryService;
    protected $notificationService;

    public function __construct(InventoryService $inventoryService, NotificationService $notificationService)
    {
        $this->inventoryService = $inventoryService;
        $this->notificationService = $notificationService;
    }

    public function processTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create transaction
            $transaction = Transaction::create([
                'order_id' => $data['order_id'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'total_amount' => $data['total_amount'],
                'payment_method' => $data['payment_method'],
                'staff_id' => Auth::id(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Process transaction items
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}, Requested: {$item['quantity']}");
                }

                // Create transaction item
                $transaction->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $item['quantity'],
                ]);

                // Reduce inventory
                $this->inventoryService->reduceStock(
                    $product->id,
                    $item['quantity'],
                    'sale',
                    $transaction->id
                );
            }

            // Send SMS notification if customer phone is provided
            if ($data['customer_phone']) {
                $this->notificationService->sendTransactionConfirmation($transaction);
            }

            // Mark linked order as completed if exists
            if (!empty($data['order_id'])) {
                $order = Order::find($data['order_id']);
                if ($order) {
                    $order->markAsCompleted();
                }
            }

            // Dispatch real-time event
            broadcast(new TransactionCompleted($transaction))->toOthers();

            return $transaction->load(['items.product', 'staff']);
        });
    }

    public function refundTransaction(Transaction $transaction, array $data)
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Update transaction status
            $transaction->update([
                'status' => 'refunded',
                'notes' => ($transaction->notes ?? '') . "\nRefund Reason: " . ($data['reason'] ?? 'No reason provided'),
            ]);

            // Restore inventory
            foreach ($transaction->items as $item) {
                $this->inventoryService->addStock(
                    $item->product_id,
                    $item->quantity,
                    'refund',
                    $transaction->id
                );
            }

            // Send refund notification
            if ($transaction->customer_phone) {
                $this->notificationService->sendRefundConfirmation($transaction);
            }

            return $transaction->fresh(['items.product', 'staff']);
        });
    }

    public function getTransaction($id)
    {
        return Transaction::with(['items.product', 'staff'])->findOrFail($id);
    }

    public function listTransactions(array $filters = [])
    {
        $query = Transaction::with(['items.product', 'staff'])
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($filters['customer_phone'] ?? null, function ($query, $phone) {
                $query->where('customer_phone', 'like', "%{$phone}%");
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getSalesReport(array $filters = [])
    {
        $query = Transaction::query()
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->where('status', 'completed');

        return [
            'total_sales' => $query->sum('total_amount'),
            'total_transactions' => $query->count(),
            'average_transaction' => $query->avg('total_amount'),
            'payment_methods' => $query->select('payment_method', DB::raw('count(*) as count, sum(total_amount) as total'))
                ->groupBy('payment_method')
                ->get(),
        ];
    }
}