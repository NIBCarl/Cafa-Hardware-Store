<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Events\InventoryUpdated;
use App\Events\LowStockAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function reduceStock(int $productId, int $quantity, string $referenceType, int $referenceId)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            if ($product->stock_quantity < $quantity) {
                throw new \Exception("Insufficient stock for product: {$product->name}");
            }

            // Update product stock
            $product->decrement('stock_quantity', $quantity);

            // Record inventory movement
            InventoryMovement::create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'type' => 'out',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'staff_id' => Auth::id(),
            ]);

            $product->refresh();

            // Dispatch real-time inventory update
            broadcast(new InventoryUpdated($product, 'reduction', $quantity))->toOthers();

            // Check for low stock and send notification
            if ($product->stock_quantity <= $product->low_stock_threshold) {
                $this->notificationService->sendLowStockAlert($product);
                broadcast(new LowStockAlert($product))->toOthers();
            }

            return $product;
        });
    }

    public function addStock(int $productId, int $quantity, string $referenceType, int $referenceId, ?string $notes = null)
    {
        return DB::transaction(function () use ($productId, $quantity, $referenceType, $referenceId, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            // Update product stock
            $product->increment('stock_quantity', $quantity);

            // Record inventory movement
            InventoryMovement::create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'type' => 'in',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'staff_id' => Auth::id(),
                'notes' => $notes,
            ]);

            $product->refresh();

            // Dispatch real-time inventory update
            broadcast(new InventoryUpdated($product, 'addition', $quantity))->toOthers();

            return $product;
        });
    }

    public function adjustStock(int $productId, int $newQuantity, ?string $notes = null)
    {
        return DB::transaction(function () use ($productId, $newQuantity, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $currentQuantity = $product->stock_quantity;
            $difference = $newQuantity - $currentQuantity;

            if ($difference === 0) {
                return $product;
            }

            // Update product stock
            $product->update(['stock_quantity' => $newQuantity]);

            // Record inventory movement
            InventoryMovement::create([
                'product_id' => $productId,
                'quantity' => abs($difference),
                'type' => $difference > 0 ? 'in' : 'out',
                'reference_type' => 'adjustment',
                'reference_id' => Auth::id(),
                'staff_id' => Auth::id(),
                'notes' => $notes ?? "Stock adjusted from {$currentQuantity} to {$newQuantity}",
            ]);

            $product->refresh();

            // Dispatch real-time inventory update
            broadcast(new InventoryUpdated($product, 'adjustment', abs($difference)))->toOthers();

            // Check for low stock
            if ($newQuantity <= $product->low_stock_threshold) {
                $this->notificationService->sendLowStockAlert($product);
                broadcast(new LowStockAlert($product))->toOthers();
            }

            return $product;
        });
    }

    public function getLowStockProducts()
    {
        return Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->with('category')
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    public function getInventoryReport(array $filters = [])
    {
        $query = Product::with('category')
            ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            });

        $products = $query->get();

        return [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->stock_quantity * $product->cost;
            }),
            'total_retail_value' => $products->sum(function ($product) {
                return $product->stock_quantity * $product->price;
            }),
            'low_stock_items' => $products->filter(function ($product) {
                return $product->stock_quantity <= $product->low_stock_threshold;
            })->count(),
            'out_of_stock_items' => $products->where('stock_quantity', 0)->count(),
        ];
    }

    public function getInventoryMovements(int $productId, array $filters = [])
    {
        return InventoryMovement::with(['product', 'staff'])
            ->where('product_id', $productId)
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }
}