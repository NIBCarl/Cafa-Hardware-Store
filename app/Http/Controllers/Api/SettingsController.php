<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get SMS settings
     */
    public function getSms()
    {
        $settings = Setting::get('sms', [
            'api_key' => '',
            'templates' => [
                'order_confirmation' => '',
                'low_stock' => ''
            ]
        ]);

        return response()->json($settings);
    }

    /**
     * Update SMS settings
     */
    public function updateSms(Request $request)
    {
        $validated = $request->validate([
            'api_key' => 'nullable|string|max:255',
            'templates' => 'nullable|array',
            'templates.order_confirmation' => 'nullable|string',
            'templates.low_stock' => 'nullable|string',
        ]);

        Setting::set('sms', $validated);

        return response()->json([
            'message' => 'SMS settings updated successfully',
            'settings' => $validated
        ]);
    }

    /**
     * Get system settings
     */
    public function getSystem()
    {
        $settings = Setting::get('system', [
            'store_name' => '',
            'contact_number' => '',
            'address' => '',
            'tax_rate' => 12
        ]);

        return response()->json($settings);
    }

    /**
     * Update system settings
     */
    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        Setting::set('system', $validated);

        return response()->json([
            'message' => 'System settings updated successfully',
            'settings' => $validated
        ]);
    }

    /**
     * Get payment settings
     */
    public function getPayment()
    {
        $settings = Setting::get('payment', [
            'gcash_enabled' => true,
            'gcash_number' => '',
            'gcash_name' => '',
        ]);

        return response()->json($settings);
    }

    /**
     * Update payment settings
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'gcash_enabled' => 'required|boolean',
            'gcash_number' => 'required_if:gcash_enabled,true|nullable|string|max:20',
            'gcash_name' => 'required_if:gcash_enabled,true|nullable|string|max:255',
        ]);

        Setting::set('payment', $validated);

        return response()->json([
            'message' => 'Payment settings updated successfully',
            'settings' => $validated
        ]);
    }
}
