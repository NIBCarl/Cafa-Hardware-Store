<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "Testing SMS-Gate.app API...\n\n";

// Test 1: Check device status
echo "Test 1: Device Status Endpoint\n";
try {
    $response = Http::timeout(10)
        ->withBasicAuth('DMQTCR', '1p8fs-1-90ahbr')
        ->get('https://api.sms-gate.app/3rdparty/v1/device/Skm6BLLt6Mhi9gHtDLprp');
    
    echo "Status Code: " . $response->status() . "\n";
    echo "Response: " . $response->body() . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test 2: Send SMS (Try Format 1 - phoneNumbers array)
echo "Test 2: Send SMS Endpoint (Format 1 - phoneNumbers)\n";
try {
    $response = Http::timeout(30)
        ->withBasicAuth('DMQTCR', '1p8fs-1-90ahbr')
        ->withHeaders(['Content-Type' => 'application/json'])
        ->post('https://api.sms-gate.app/3rdparty/v1/message', [
            'deviceId' => 'Skm6BLLt6Mhi9gHtDLprp',
            'phoneNumbers' => ['+639631065271'],
            'message' => 'Test SMS from CAFA POS via SMS-Gate.app'
        ]);
    
    echo "Status Code: " . $response->status() . "\n";
    echo "Response: " . $response->body() . "\n\n";
    
    if ($response->successful()) {
        echo "✅ SMS sent successfully!\n";
        echo "Check your phone at 09631065271\n";
    } else {
        echo "❌ Failed to send SMS\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "\nDone!\n";
