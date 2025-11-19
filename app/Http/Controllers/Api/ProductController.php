<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc');

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'barcode' => 'nullable|string|unique:products',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
                $validated['image'] = $imagePath;
            }

            $product = Product::create($validated);
            return response()->json($product->load('category'), 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            throw $e;
        }
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'sku' => 'string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image && \Storage::disk('public')->exists($product->image)) {
                    \Storage::disk('public')->delete($product->image);
                }
                
                $imagePath = $request->file('image')->store('products', 'public');
                $validated['image'] = $imagePath;
            }

            $product->update($validated);
            return response()->json($product->fresh('category'));
        } catch (\Exception $e) {
            Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'data' => $validated
            ]);
            throw $e;
        }
    }

    public function destroy(Product $product)
    {
        if (request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            if ($product->transactionItems()->exists()) {
                throw ValidationException::withMessages([
                    'product' => ['Cannot delete product with existing transactions']
                ]);
            }

            // Delete product image if exists
            if ($product->image && \Storage::disk('public')->exists($product->image)) {
                \Storage::disk('public')->delete($product->image);
            }

            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Product deletion failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);
            throw $e;
        }
    }

    public function adjustStock(Request $request, Product $product)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            $oldQuantity = $product->stock_quantity;
            $newQuantity = $validated['new_quantity'];
            $difference = $newQuantity - $oldQuantity;

            $product->update(['stock_quantity' => $newQuantity]);

            $product->inventoryMovements()->create([
                'quantity' => abs($difference),
                'type' => $difference > 0 ? 'in' : 'out',
                'reference_type' => 'adjustment',
                'reference_id' => 0,
                'staff_id' => $request->user()->id,
                'notes' => $validated['notes']
            ]);

            return response()->json($product->fresh());
        } catch (\Exception $e) {
            Log::error('Stock adjustment failed', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'data' => $validated
            ]);
            throw $e;
        }
    }

    public function lowStock()
    {
        $products = Product::where('is_active', true)
            ->whereRaw('stock_quantity <= low_stock_threshold')
            ->with('category')
            ->get();

        return response()->json($products);
    }
}