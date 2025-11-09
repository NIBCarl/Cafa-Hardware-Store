<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }
    public function index(Request $request)
    {
        $query = Transaction::with(['items.product', 'staff'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->when($request->customer_phone, function ($query, $phone) {
                $query->where('customer_phone', 'like', "%{$phone}%");
            })
            ->latest();

        return response()->json($query->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'customer_phone' => 'nullable|string',
            'payment_method' => 'required|in:cash,card,digital_wallet',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0'
        ]);

        try {
            $transaction = $this->transactionService->processTransaction($validated);

            return response()->json($transaction, 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Transaction creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            
            return response()->json([
                'message' => 'Failed to process transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        return response()->json(
            $transaction->load(['items.product', 'staff'])
        );
    }

    public function refund(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            throw ValidationException::withMessages([
                'transaction' => ['Only completed transactions can be refunded']
            ]);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string'
        ]);

        try {
            $refundedTransaction = $this->transactionService->refundTransaction($transaction, $validated);

            return response()->json($refundedTransaction);
        } catch (\Exception $e) {
            Log::error('Transaction refund failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
                'data' => $validated
            ]);
            
            return response()->json([
                'message' => 'Failed to process refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}