<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\NotificationService;
use App\Events\OrderStatusChanged;
use App\Events\InventoryUpdated;
use App\Events\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $customer = $request->user();

        $orders = Order::with(['items.product'])
            ->forCustomer($customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,digital_wallet,gcash',
            'payment_proof' => 'required_if:payment_method,gcash,digital_wallet|nullable|file|mimes:jpg,jpeg,png|max:5120',
            'delivery_method' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:delivery_method,delivery|nullable|string',
            'notes' => 'nullable|string',
        ]);

        $customer = $request->user();

        return DB::transaction(function () use ($validated, $customer, $request) {
            // Calculate total and validate stock
            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if (!$product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ["Product {$product->name} is no longer available."],
                    ]);
                }

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for {$product->name}. Available: {$product->stock_quantity}"],
                    ]);
                }

                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            // Handle payment proof upload for GCash/digital wallet
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
                try {
                    $file = $request->file('payment_proof');
                    $orderNumber = 'ORD-' . strtoupper(uniqid());
                    $extension = $file->getClientOriginalExtension();
                    
                    // Ensure the payment-proofs directory exists
                    $directory = storage_path('app/public/payment-proofs');
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    
                    $filename = "payment-proofs/{$orderNumber}.{$extension}";
                    
                    // Store the file using the 'public' disk explicitly
                    $storedPath = $file->storeAs('payment-proofs', "{$orderNumber}.{$extension}", 'public');
                    
                    if ($storedPath) {
                        // Path is already relative to public disk
                        $paymentProofPath = $storedPath;
                        \Log::info('Payment proof uploaded', [
                            'stored_path' => $storedPath,
                            'db_path' => $paymentProofPath,
                            'full_path' => storage_path('app/public/' . $storedPath)
                        ]);
                    } else {
                        \Log::error('Failed to store payment proof file');
                    }
                } catch (\Exception $e) {
                    \Log::error('Payment proof upload error', ['error' => $e->getMessage()]);
                    throw $e;
                }
            } else {
                \Log::info('Payment proof upload check', [
                    'has_file' => $request->hasFile('payment_proof'),
                    'is_valid' => $request->hasFile('payment_proof') ? $request->file('payment_proof')->isValid() : false,
                    'payment_method' => $validated['payment_method']
                ]);
            }

            // Create order
            $order = Order::create([
                'customer_id' => $customer->id,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'payment_proof' => $paymentProofPath,
                'delivery_method' => $validated['delivery_method'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items AND reduce inventory
            foreach ($orderItems as $item) {
                $order->items()->create($item);
                
                // Reduce product stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock_quantity', $item['quantity']);
                
                // Create inventory movement record
                InventoryMovement::create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'type' => 'out',
                    'reference_type' => 'customer_order',
                    'reference_id' => $order->id,
                    'staff_id' => null, // Customer order, no staff
                    'notes' => "Customer order: {$order->order_number}",
                ]);
                
                // Refresh product and broadcast inventory update
                $product->refresh();
                broadcast(new InventoryUpdated($product, 'reduction', (int) $item['quantity']))->toOthers();
                
                // Check for low stock alert
                if ($product->stock_quantity <= $product->low_stock_threshold) {
                    broadcast(new LowStockAlert($product))->toOthers();
                }
            }

            // Send SMS confirmation
            $this->notificationService->sendOrderConfirmation($order);

            // Dispatch real-time event
            broadcast(new OrderStatusChanged($order, 'new', 'pending'))->toOthers();

            $message = 'Order placed successfully';
            if ($order->requiresPaymentProof()) {
                $message .= '. Payment verification pending.';
            }

            return response()->json([
                'message' => $message,
                'order' => $order->load(['items.product']),
                'payment_proof_url' => $order->payment_proof_url,
            ], 201);
        });
    }

    public function show(Request $request, Order $order)
    {
        $customer = $request->user();

        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'order' => $order->load(['items.product']),
        ]);
    }

    public function cancel(Request $request, Order $order)
    {
        $customer = $request->user();

        if ($order->customer_id !== $customer->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$order->isPending()) {
            return response()->json([
                'message' => 'Only pending orders can be cancelled',
            ], 400);
        }

        $oldStatus = $order->status;
        
        // Restore inventory for cancelled order
        foreach ($order->items as $item) {
            $product = $item->product;
            $product->increment('stock_quantity', $item->quantity);
            
            // Create inventory movement record
            InventoryMovement::create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'type' => 'in',
                'reference_type' => 'order_cancellation',
                'reference_id' => $order->id,
                'staff_id' => null,
                'notes' => "Order cancelled: {$order->order_number}",
            ]);
            
            // Broadcast inventory update
            $product->refresh();
            broadcast(new InventoryUpdated($product, 'addition', (int) $item->quantity))->toOthers();
        }
        
        $order->cancel();

        // Dispatch real-time event
        broadcast(new OrderStatusChanged($order->fresh(), $oldStatus, 'cancelled'))->toOthers();

        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $order->fresh(['items.product']),
        ]);
    }
}
