<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->has('is_active'), function ($query) use ($request) {
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
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $category = Category::create($validated);
            return response()->json($category, 201);
        } catch (\Exception $e) {
            Log::error('Category creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            throw $e;
        }
    }

    public function show(Category $category)
    {
        return response()->json($category->load('products'));
    }

    public function update(Request $request, Category $category)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $category->update($validated);
            return response()->json($category->fresh());
        } catch (\Exception $e) {
            Log::error('Category update failed', [
                'error' => $e->getMessage(),
                'category_id' => $category->id,
                'data' => $validated
            ]);
            throw $e;
        }
    }

    public function destroy(Category $category)
    {
        if (request()->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            if ($category->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => ['Cannot delete category with existing products']
                ]);
            }

            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Category deletion failed', [
                'error' => $e->getMessage(),
                'category_id' => $category->id
            ]);
            throw $e;
        }
    }
}