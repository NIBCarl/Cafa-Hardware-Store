<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function stats(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();

        // Calculate previous period for trends
        $periodDays = $start->diffInDays($end);
        $prevStart = $start->copy()->subDays($periodDays);
        $prevEnd = $start->copy()->subDay();

        // Current period stats
        $currentSales = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->sum('total_amount');
        
        $currentTransactions = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->count();

        // Previous period stats for trends
        $previousSales = Transaction::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'completed')
            ->sum('total_amount');
        
        $previousTransactions = Transaction::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', 'completed')
            ->count();

        // Calculate trends
        $salesTrend = $previousSales > 0 
            ? (($currentSales - $previousSales) / $previousSales) * 100 
            : 0;
        
        $transactionsTrend = $previousTransactions > 0 
            ? (($currentTransactions - $previousTransactions) / $previousTransactions) * 100 
            : 0;

        // Average transaction value
        $avgTransactionValue = $currentTransactions > 0 
            ? $currentSales / $currentTransactions 
            : 0;
        
        $prevAvgTransactionValue = $previousTransactions > 0 
            ? $previousSales / $previousTransactions 
            : 0;
        
        $avgTransactionTrend = $prevAvgTransactionValue > 0 
            ? (($avgTransactionValue - $prevAvgTransactionValue) / $prevAvgTransactionValue) * 100 
            : 0;

        // Low stock items
        $lowStockItems = Product::where('is_active', true)
            ->whereRaw('stock_quantity <= low_stock_threshold')
            ->count();

        return response()->json([
            'totalSales' => (float) $currentSales,
            'salesTrend' => round($salesTrend, 2),
            'totalTransactions' => $currentTransactions,
            'transactionsTrend' => round($transactionsTrend, 2),
            'averageTransactionValue' => round($avgTransactionValue, 2),
            'avgTransactionTrend' => round($avgTransactionTrend, 2),
            'lowStockItems' => $lowStockItems,
        ]);
    }

    public function salesTrend(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();
        $interval = $request->interval ?? 'daily';

        // MySQL date grouping
        $groupBy = match($interval) {
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
            'weekly' => "DATE_FORMAT(created_at, '%Y-%u')",
            default => "DATE(created_at)",
        };

        $sales = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->select(
                DB::raw("$groupBy as date"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $sales->pluck('date')->toArray();
        $values = $sales->pluck('total')->map(fn($v) => (float) $v)->toArray();

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function topProducts(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();

        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.quantity * transaction_items.price) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        return response()->json([
            'labels' => $topProducts->pluck('name')->toArray(),
            'values' => $topProducts->pluck('total_sales')->map(fn($v) => (float) $v)->toArray(),
        ]);
    }

    public function categorySales(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();

        $categorySales = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->select(
                DB::raw('COALESCE(categories.name, "Uncategorized") as name'),
                DB::raw('SUM(transaction_items.quantity * transaction_items.price) as total_sales')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales')
            ->get();

        return response()->json([
            'labels' => $categorySales->pluck('name')->toArray(),
            'values' => $categorySales->pluck('total_sales')->map(fn($v) => (float) $v)->toArray(),
        ]);
    }

    public function transactions(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();

        $transactions = Transaction::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json($transactions);
    }

    public function export(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfDay();

        $transactions = Transaction::with(['items.product', 'staff'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();

        // Generate CSV content
        $csvData = "Transaction ID,Date,Customer Phone,Payment Method,Status,Total Amount,Staff,Items\n";
        
        foreach ($transactions as $transaction) {
            $items = $transaction->items->map(function ($item) {
                return $item->product->name . ' (x' . $item->quantity . ')';
            })->join('; ');
            
            $csvData .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,\"%s\"\n",
                $transaction->id,
                $transaction->created_at->format('Y-m-d H:i:s'),
                $transaction->customer_phone ?? 'N/A',
                ucwords(str_replace('_', ' ', $transaction->payment_method)),
                ucfirst($transaction->status),
                number_format($transaction->total_amount, 2),
                $transaction->staff->name ?? 'N/A',
                $items
            );
        }

        return response($csvData, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="transactions-report-' . $start->format('Y-m-d') . '-to-' . $end->format('Y-m-d') . '.csv"');
    }
}
