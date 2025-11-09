<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AndroidSmsService
{
    protected $gatewayUrl;
    protected $apiToken;
    protected $timeout;
    protected $enabled;

    protected $username;
    protected $password;
    protected $deviceId;

    public function __construct()
    {
        $this->gatewayUrl = config('services.android_sms.gateway_url', 'https://api.sms-gate.app');
        $this->username = config('services.android_sms.username');
        $this->password = config('services.android_sms.password');
        $this->deviceId = config('services.android_sms.device_id');
        $this->apiToken = config('services.android_sms.api_token'); // Keep for backward compatibility
        $this->timeout = config('services.android_sms.timeout', 30);
        $this->enabled = config('services.android_sms.enabled', false);
    }

    /**
     * Send SMS via Android Gateway
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function send(string $phoneNumber, string $message): bool
    {
        if (!$this->enabled) {
            Log::warning('Android SMS Gateway not enabled. SMS not sent.', [
                'phone' => $phoneNumber,
            ]);
            return false;
        }

        // Check if using SMS-Gate.app (cloud) or local gateway
        $isSmsGateApp = $this->username && $this->password && $this->deviceId;
        $isLocalGateway = $this->apiToken;

        if (!$isSmsGateApp && !$isLocalGateway) {
            Log::warning('Android SMS Gateway not configured. SMS not sent.', [
                'phone' => $phoneNumber,
                'sms_gate_app_configured' => $isSmsGateApp,
                'local_gateway_configured' => $isLocalGateway,
            ]);
            return false;
        }

        // Normalize phone number
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        try {
            if ($isSmsGateApp) {
                // Use SMS-Gate.app API
                return $this->sendViaSmsGateApp($phoneNumber, $message);
            } else {
                // Use local Android gateway API
                return $this->sendViaLocalGateway($phoneNumber, $message);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Android SMS Gateway connection failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'gateway_url' => $this->gatewayUrl,
            ]);

            $this->markOffline();
            return false;
        } catch (\Exception $e) {
            Log::error('Exception while sending Android SMS', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Send SMS via SMS-Gate.app cloud service
     */
    protected function sendViaSmsGateApp(string $phoneNumber, string $message): bool
    {
        // SMS-Gate.app requires phone numbers with + prefix
        if (substr($phoneNumber, 0, 1) !== '+') {
            $phoneNumber = '+' . $phoneNumber;
        }

        $response = Http::timeout($this->timeout)
            ->withBasicAuth($this->username, $this->password)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->gatewayUrl . '/3rdparty/v1/message', [
                'deviceId' => $this->deviceId,
                'phoneNumbers' => [$phoneNumber],
                'message' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            
            Log::info('SMS sent successfully via SMS-Gate.app', [
                'phone' => $phoneNumber,
                'message_id' => $data['id'] ?? $data['message_id'] ?? null,
                'state' => $data['state'] ?? 'sent',
            ]);

            $this->markOnline();
            return true;
        } else {
            Log::error('Failed to send SMS via SMS-Gate.app', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            if ($response->status() >= 500) {
                $this->markOffline();
            }

            return false;
        }
    }

    /**
     * Send SMS via local Android gateway
     */
    protected function sendViaLocalGateway(string $phoneNumber, string $message): bool
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ])
            ->post($this->gatewayUrl . '/api/send', [
                'phone' => $phoneNumber,
                'message' => $message,
            ]);

        if ($response->successful()) {
            $data = $response->json();
            
            Log::info('SMS sent successfully via local gateway', [
                'phone' => $phoneNumber,
                'message_id' => $data['id'] ?? null,
                'status' => $data['status'] ?? 'sent',
            ]);

            $this->markOnline();
            return true;
        } else {
            Log::error('Failed to send SMS via local gateway', [
                'phone' => $phoneNumber,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            if ($response->status() >= 500) {
                $this->markOffline();
            }

            return false;
        }
    }

    /**
     * Send SMS to multiple recipients
     *
     * @param array $phoneNumbers
     * @param string $message
     * @return bool
     */
    public function sendBulk(array $phoneNumbers, string $message): bool
    {
        if (!$this->enabled) {
            Log::warning('Android SMS Gateway not configured. Bulk SMS not sent.');
            return false;
        }

        $phoneNumbers = array_map([$this, 'normalizePhoneNumber'], $phoneNumbers);

        // For bulk sending, send individually (SMS-Gate.app doesn't have bulk endpoint)
        $successCount = 0;
        $failCount = 0;

        foreach ($phoneNumbers as $phone) {
            if ($this->send($phone, $message)) {
                $successCount++;
            } else {
                $failCount++;
            }
            
            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 second delay
        }

        Log::info('Bulk SMS completed', [
            'total' => count($phoneNumbers),
            'success' => $successCount,
            'failed' => $failCount,
        ]);

        // Consider successful if at least half succeeded
        return $successCount > 0;
    }

    /**
     * Check if Android Gateway is online
     *
     * @return bool
     */
    public function isOnline(): bool
    {
        if (!$this->enabled) {
            return false;
        }

        // Check cache first
        $cachedStatus = Cache::get('android_sms_gateway_status');
        if ($cachedStatus !== null) {
            return $cachedStatus === 'online';
        }

        // Ping the gateway
        try {
            $isSmsGateApp = $this->username && $this->password && $this->deviceId;
            
            if ($isSmsGateApp) {
                // Ping SMS-Gate.app - use message list endpoint as device endpoint returns 404
                $response = Http::timeout(5)
                    ->withBasicAuth($this->username, $this->password)
                    ->get($this->gatewayUrl . '/3rdparty/v1/message');
            } else {
                // Ping local gateway
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                    ])
                    ->get($this->gatewayUrl . '/api/status');
            }

            if ($response->successful()) {
                $this->markOnline();
                return true;
            }
        } catch (\Exception $e) {
            Log::debug('Android SMS Gateway ping failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $this->markOffline();
        return false;
    }

    /**
     * Get gateway status information
     *
     * @return array
     */
    public function getStatus(): array
    {
        if (!$this->enabled) {
            return [
                'online' => false,
                'message' => 'Gateway not enabled',
                'type' => 'disabled',
            ];
        }

        $isSmsGateApp = $this->username && $this->password && $this->deviceId;

        try {
            if ($isSmsGateApp) {
                // Get status from SMS-Gate.app - use message list endpoint
                $response = Http::timeout(5)
                    ->withBasicAuth($this->username, $this->password)
                    ->get($this->gatewayUrl . '/3rdparty/v1/message');

                if ($response->successful()) {
                    $this->markOnline();

                    return [
                        'online' => true,
                        'type' => 'sms-gate.app',
                        'device_id' => $this->deviceId,
                        'name' => 'SMS-Gate.app Cloud Service',
                        'state' => 'connected',
                        'message' => 'API accessible and ready',
                    ];
                }
            } else {
                // Get status from local gateway
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                    ])
                    ->get($this->gatewayUrl . '/api/status');

                if ($response->successful()) {
                    $data = $response->json();
                    $this->markOnline();

                    return [
                        'online' => true,
                        'type' => 'local',
                        'device' => $data['device'] ?? 'Unknown',
                        'battery' => $data['battery'] ?? null,
                        'signal' => $data['signal'] ?? null,
                        'last_message' => $data['last_message'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::debug('Failed to get Android gateway status', [
                'error' => $e->getMessage(),
            ]);
        }

        $this->markOffline();

        return [
            'online' => false,
            'type' => $isSmsGateApp ? 'sms-gate.app' : 'local',
            'message' => 'Gateway offline or unreachable',
        ];
    }

    /**
     * Normalize phone number to Philippine format
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If number starts with 0, replace with 63 (Philippines country code)
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '63' . substr($phoneNumber, 1);
        }

        // If number doesn't start with 63, prepend it
        if (substr($phoneNumber, 0, 2) !== '63') {
            $phoneNumber = '63' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Validate Philippine phone number
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $normalized = $this->normalizePhoneNumber($phoneNumber);
        
        // Philippine mobile numbers should be 12 digits starting with 63
        return strlen($normalized) === 12 && substr($normalized, 0, 2) === '63';
    }

    /**
     * Mark gateway as online in cache
     */
    protected function markOnline(): void
    {
        Cache::put('android_sms_gateway_status', 'online', now()->addMinutes(5));
    }

    /**
     * Mark gateway as offline in cache
     */
    protected function markOffline(): void
    {
        Cache::put('android_sms_gateway_status', 'offline', now()->addMinutes(1));
    }
}
