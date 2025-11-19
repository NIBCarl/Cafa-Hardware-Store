<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderManagementController;
use App\Http\Controllers\Api\PaymentInfoController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SmsGatewayStatusController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Api\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\Customer\ProductController as CustomerProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - Staff
Route::post('/login', [AuthController::class, 'login']);

// Public payment information
Route::get('/payment-info', [PaymentInfoController::class, 'index']);

// Customer Portal - Public routes
Route::prefix('customer')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
    
    // Public product browsing
    Route::get('/products', [CustomerProductController::class, 'index']);
    Route::get('/products/{product}', [CustomerProductController::class, 'show']);
    Route::get('/products/featured/list', [CustomerProductController::class, 'featured']);
    Route::get('/categories', [CustomerProductController::class, 'categories']);
});

// Customer Portal - Protected routes
Route::prefix('customer')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/profile', [CustomerAuthController::class, 'profile']);
    Route::put('/profile', [CustomerAuthController::class, 'updateProfile']);
    Route::post('/change-password', [CustomerAuthController::class, 'changePassword']);
    
    // Orders
    Route::get('/orders', [CustomerOrderController::class, 'index']);
    Route::post('/orders', [CustomerOrderController::class, 'store']);
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show']);
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);
});

// Staff Portal - Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Product routes
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);
    Route::apiResource('products', ProductController::class);

    // Category routes
    Route::apiResource('categories', CategoryController::class);

    // Transaction routes
    Route::post('/transactions/{transaction}/refund', [TransactionController::class, 'refund']);
    Route::apiResource('transactions', TransactionController::class);

    // Report routes
    Route::get('/reports/stats', [ReportController::class, 'stats']);
    Route::get('/reports/sales-trend', [ReportController::class, 'salesTrend']);
    Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
    Route::get('/reports/category-sales', [ReportController::class, 'categorySales']);
    Route::get('/reports/transactions', [ReportController::class, 'transactions']);
    Route::get('/reports/export', [ReportController::class, 'export']);

    // Order Management routes
    Route::get('/orders/stats', [OrderManagementController::class, 'stats']);
    Route::get('/orders/pending-verification', [OrderManagementController::class, 'pendingVerification']);
    Route::post('/orders/{order}/update-status', [OrderManagementController::class, 'updateStatus']);
    Route::post('/orders/{order}/verify-payment', [OrderManagementController::class, 'verifyPayment']);
    Route::post('/orders/{order}/cancel', [OrderManagementController::class, 'cancel']);
    Route::apiResource('orders', OrderManagementController::class)->only(['index', 'show']);

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Settings routes
        Route::get('/settings/sms', [SettingsController::class, 'getSms']);
        Route::post('/settings/sms', [SettingsController::class, 'updateSms']);
        Route::get('/settings/system', [SettingsController::class, 'getSystem']);
        Route::post('/settings/system', [SettingsController::class, 'updateSystem']);
        Route::get('/settings/payment', [SettingsController::class, 'getPayment']);
        Route::post('/settings/payment', [SettingsController::class, 'updatePayment']);

        // SMS Gateway routes
        Route::get('/sms/status', [SmsGatewayStatusController::class, 'getStatus']);
        Route::post('/sms/test', [SmsGatewayStatusController::class, 'testSend']);
        Route::get('/sms/ping-android', [SmsGatewayStatusController::class, 'pingAndroidGateway']);

        // User Management routes
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::apiResource('users', UserController::class);

        // Customer Management routes
        Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus']);
        Route::get('/customers/stats', [CustomerController::class, 'stats']);
        Route::apiResource('customers', CustomerController::class);
    });
});