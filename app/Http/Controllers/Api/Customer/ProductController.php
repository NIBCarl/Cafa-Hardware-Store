<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        $allowedSorts = ['name', 'price', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $products = $query->paginate($request->input('per_page', 12));

        return response()->json($products);
    }

    public function show(Product $product)
    {
        if (!$product->is_active) {
            return response()->json([
                'message' => 'Product not available',
            ], 404);
        }

        return response()->json([
            'product' => $product->load('category'),
        ]);
    }

    public function categories()
    {
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('is_active', true);
        }])
            ->having('products_count', '>', 0)
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function featured(Request $request)
    {
        // Return top-selling or featured products
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->inRandomOrder()
            ->limit($request->input('limit', 8))
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
