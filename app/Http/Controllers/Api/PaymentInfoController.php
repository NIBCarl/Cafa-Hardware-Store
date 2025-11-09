<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class PaymentInfoController extends Controller
{
    /**
     * Get public payment information (no authentication required)
     */
    public function index()
    {
        $paymentSettings = Setting::get('payment', [
            'gcash_enabled' => false,
            'gcash_number' => '',
            'gcash_name' => '',
        ]);

        return response()->json([
            'gcash' => [
                'enabled' => $paymentSettings['gcash_enabled'] ?? false,
                'number' => $paymentSettings['gcash_number'] ?? '',
                'name' => $paymentSettings['gcash_name'] ?? '',
            ]
        ]);
    }
}
