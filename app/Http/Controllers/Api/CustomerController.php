<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by verified status
        if ($request->has('email_verified')) {
            if ($request->boolean('email_verified')) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $customers = $query->withCount('orders')
                          ->orderBy('created_at', 'desc')
                          ->paginate($request->get('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:20|unique:customers,phone',
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Set is_active to true by default if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer,
        ], 201);
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load(['orders' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return response()->json([
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('customers')->ignore($customer->id)],
            'phone' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('customers')->ignore($customer->id)],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has orders
        if ($customer->orders()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete customer with existing orders',
            ], 403);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully',
        ]);
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);

        return response()->json([
            'message' => 'Customer status updated successfully',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Get customer statistics
     */
    public function stats()
    {
        $stats = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where('is_active', true)->count(),
            'inactive_customers' => Customer::where('is_active', false)->count(),
            'verified_customers' => Customer::whereNotNull('email_verified_at')->count(),
            'customers_with_orders' => Customer::has('orders')->count(),
        ];

        return response()->json($stats);
    }
}
