<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends Controller
{
    /**
     * Display a listing of customer orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.product', 'verifiedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by payment verification status
        if ($request->has('pending_verification') && $request->pending_verification === 'true') {
            $query->where('payment_status', 'pending')
                  ->whereNotNull('payment_proof');
        }

        // Filter by status (supports single or comma-separated multiple statuses)
        if ($request->has('status') && $request->status !== '') {
            $statuses = explode(',', $request->status);
            if (count($statuses) > 1) {
                $query->whereIn('status', $statuses);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search by order number or customer name
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate($request->get('per_page', 15));

        // Append payment proof URLs to each order
        $orders->getCollection()->transform(function ($order) {
            $order->payment_proof_url = $order->payment_proof_url;
            $order->requires_payment_proof = $order->requiresPaymentProof();
            $order->is_payment_verified = $order->isPaymentVerified();
            return $order;
        });

        return response()->json($orders);
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'verifiedBy']);
        
        // Add payment proof URL if exists
        $orderData = $order->toArray();
        $orderData['payment_proof_url'] = $order->payment_proof_url;
        $orderData['requires_payment_proof'] = $order->requiresPaymentProof();
        $orderData['is_payment_verified'] = $order->isPaymentVerified();
        
        return response()->json($orderData);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,ready,completed,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // TODO: Add activity logging if needed (requires spatie/laravel-activitylog package)
        // Log the status change for audit purposes
        \Log::info('Order status updated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'changed_by' => auth()->user()->name,
            'changed_by_id' => auth()->id(),
        ]);

        // Broadcast the status change event
        broadcast(new \App\Events\OrderStatusChanged($order, $oldStatus, $request->status))->toOthers();

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['customer', 'items.product'])
        ]);
    }

    /**
     * Cancel an order (admin override)
     */
    public function cancel(Order $order)
    {
        if ($order->status === 'completed') {
            return response()->json([
                'message' => 'Cannot cancel a completed order'
            ], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Order is already cancelled'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Restore inventory
            foreach ($order->items as $item) {
                $product = $item->product;
                $product->increment('stock_quantity', $item->quantity);
                
                // Create inventory movement record
                \App\Models\InventoryMovement::create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'type' => 'in',
                    'reference_type' => 'order_cancellation',
                    'reference_id' => $order->id,
                    'staff_id' => auth()->id(),
                    'notes' => "Order cancelled by staff: {$order->order_number}",
                ]);

                // Broadcast inventory update
                $product->refresh();
                broadcast(new \App\Events\InventoryUpdated($product, 'restock', (int) $item->quantity))->toOthers();
            }

            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->load(['customer', 'items.product'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics
     */
    public function stats()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'ready_orders' => Order::where('status', 'ready')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'pending_payment_verification' => Order::where('payment_status', 'pending')
                ->whereNotNull('payment_proof')
                ->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'pending_revenue' => Order::whereIn('status', ['pending', 'processing', 'ready'])->sum('total_amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Get orders pending payment verification
     */
    public function pendingVerification()
    {
        $orders = Order::with(['customer', 'items.product'])
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_proof')
            ->orderBy('created_at', 'desc')
            ->get();

        // Append payment proof URLs
        $orders->transform(function ($order) {
            $order->payment_proof_url = $order->payment_proof_url;
            return $order;
        });

        return response()->json([
            'count' => $orders->count(),
            'orders' => $orders
        ]);
    }

    /**
     * Verify payment (approve or reject)
     */
    public function verifyPayment(Request $request, Order $order)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$order->payment_proof) {
            return response()->json([
                'message' => 'No payment proof uploaded for this order'
            ], 422);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Payment already verified'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $action = $request->action;

            if ($action === 'approve') {
                // Approve payment
                $order->payment_status = 'paid';
                $order->verified_at = now();
                $order->verified_by = auth()->id();
                
                if ($request->filled('payment_reference')) {
                    $order->payment_reference = $request->payment_reference;
                }

                // Update order status to confirmed
                if ($order->status === 'pending') {
                    $order->status = 'confirmed';
                }

                $order->save();

                // Log verification
                \Log::info('Payment verified', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'verified_by' => auth()->user()->name,
                    'reference' => $request->payment_reference,
                ]);

                // Send SMS notification
                $customer = $order->customer;
                $message = "CAFA Hardware: Payment verified! Your order #{$order->order_number} is confirmed. Thank you!";
                
                try {
                    app(\App\Services\SmsService::class)->send($customer->phone, $message);
                } catch (\Exception $e) {
                    \Log::warning('Failed to send payment verification SMS', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Broadcast event
                broadcast(new \App\Events\OrderStatusChanged($order, 'pending', 'confirmed'))->toOthers();

                DB::commit();

                return response()->json([
                    'message' => 'Payment approved successfully',
                    'order' => $order->load(['customer', 'items.product', 'verifiedBy'])
                ]);

            } else {
                // Reject payment - restore inventory and cancel order
                foreach ($order->items as $item) {
                    $product = $item->product;
                    $product->increment('stock_quantity', $item->quantity);
                    
                    // Create inventory movement record
                    \App\Models\InventoryMovement::create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'type' => 'in',
                        'reference_type' => 'payment_rejection',
                        'reference_id' => $order->id,
                        'staff_id' => auth()->id(),
                        'notes' => $request->notes ?? "Payment verification failed: {$order->order_number}",
                    ]);

                    // Broadcast inventory update
                    $product->refresh();
                    broadcast(new \App\Events\InventoryUpdated($product, 'restock', (int) $item->quantity))->toOthers();
                }

                $order->status = 'cancelled';
                $order->payment_status = 'refunded';
                $order->save();

                // Log rejection
                \Log::info('Payment rejected', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'rejected_by' => auth()->user()->name,
                    'notes' => $request->notes,
                ]);

                // Send SMS notification
                $customer = $order->customer;
                $message = "CAFA Hardware: Payment verification failed for order #{$order->order_number}. Order cancelled. Please contact us if you believe this is an error.";
                
                try {
                    app(\App\Services\SmsService::class)->send($customer->phone, $message);
                } catch (\Exception $e) {
                    \Log::warning('Failed to send payment rejection SMS', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }

                // Broadcast event
                broadcast(new \App\Events\OrderStatusChanged($order, 'pending', 'cancelled'))->toOthers();

                DB::commit();

                return response()->json([
                    'message' => 'Payment rejected and order cancelled',
                    'order' => $order->load(['customer', 'items.product'])
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment verification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to verify payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
