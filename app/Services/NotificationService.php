<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Order;
use App\Services\Channels\SmsChannelFactory;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $smsChannelFactory;

    public function __construct(SmsChannelFactory $smsChannelFactory)
    {
        $this->smsChannelFactory = $smsChannelFactory;
    }

    public function sendTransactionConfirmation(Transaction $transaction)
    {
        if (!$transaction->customer_phone) {
            return false;
        }

        $message = "Thank you for your purchase at CAFA Hardware! "
            . "Transaction #" . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) . " "
            . "Total: ₱" . number_format($transaction->total_amount, 2) . ". "
            . "We appreciate your business!";

        try {
            return $this->smsChannelFactory->send($transaction->customer_phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send transaction confirmation SMS', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendRefundConfirmation(Transaction $transaction)
    {
        if (!$transaction->customer_phone) {
            return false;
        }

        $message = "CAFA Hardware: Your refund for Transaction #" 
            . str_pad($transaction->id, 6, '0', STR_PAD_LEFT) . " "
            . "has been processed. Amount: ₱" . number_format($transaction->total_amount, 2) . ". "
            . "Thank you for your understanding.";

        try {
            return $this->smsChannelFactory->send($transaction->customer_phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send refund confirmation SMS', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendLowStockAlert(Product $product)
    {
        // Get admin/manager phone numbers from settings or config
        $adminPhones = config('app.admin_phones', []);

        if (empty($adminPhones)) {
            Log::warning('No admin phone numbers configured for low stock alerts');
            return false;
        }

        $message = "⚠️ LOW STOCK ALERT: {$product->name} (SKU: {$product->sku}) "
            . "is running low. Current stock: {$product->stock_quantity} units. "
            . "Threshold: {$product->low_stock_threshold} units. Please reorder soon.";

        $results = [];
        foreach ($adminPhones as $phone) {
            try {
                $results[] = $this->smsChannelFactory->send($phone, $message);
            } catch (\Exception $e) {
                Log::error('Failed to send low stock alert SMS', [
                    'product_id' => $product->id,
                    'phone' => $phone,
                    'error' => $e->getMessage()
                ]);
                $results[] = false;
            }
        }

        return in_array(true, $results);
    }

    public function sendOrderConfirmation(Order $order)
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return false;
        }

        $message = "Thank you for your order at CAFA Hardware! "
            . "Order #{$order->order_number}. "
            . "Total: ₱" . number_format($order->total_amount, 2) . ". "
            . "Status: {$order->status}. "
            . "We'll notify you when it's ready!";

        try {
            return $this->smsChannelFactory->send($customer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation SMS', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendOrderStatusUpdate(Order $order, string $status)
    {
        $customer = $order->customer;

        if (!$customer || !$customer->phone) {
            return false;
        }

        $statusMessages = [
            'confirmed' => "Your order #{$order->order_number} has been confirmed and is being prepared.",
            'processing' => "Your order #{$order->order_number} is now being processed.",
            'ready' => "Your order #{$order->order_number} is ready for pickup! Visit us during business hours.",
            'completed' => "Thank you! Your order #{$order->order_number} has been completed.",
            'cancelled' => "Your order #{$order->order_number} has been cancelled.",
        ];

        $message = "CAFA Hardware: " . ($statusMessages[$status] ?? "Your order #{$order->order_number} status: {$status}.");

        try {
            return $this->smsChannelFactory->send($customer->phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send order status update SMS', [
                'order_id' => $order->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendOrderReady($customerPhone, $orderId)
    {
        $message = "CAFA Hardware: Your order #{$orderId} is ready for pickup! "
            . "Visit us during business hours. Thank you!";

        try {
            return $this->smsChannelFactory->send($customerPhone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send order ready SMS', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendPromotionalMessage($customerPhone, $message)
    {
        try {
            $fullMessage = "CAFA Hardware: {$message} Reply STOP to unsubscribe.";
            return $this->smsChannelFactory->send($customerPhone, $fullMessage);
        } catch (\Exception $e) {
            Log::error('Failed to send promotional SMS', [
                'phone' => $customerPhone,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function broadcastPromotion(array $phoneNumbers, string $message)
    {
        $results = [];
        foreach ($phoneNumbers as $phone) {
            $results[$phone] = $this->sendPromotionalMessage($phone, $message);
        }
        return $results;
    }
}