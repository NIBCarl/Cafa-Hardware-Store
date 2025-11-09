<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $senderName;
    protected $baseUrl = 'https://api.semaphore.co/api/v4';

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.api_key');
        $this->senderName = config('services.semaphore.sender_name', 'CAFA');
    }

    public function send(string $phoneNumber, string $message)
    {
        if (!$this->apiKey) {
            Log::warning('Semaphore API key not configured. SMS not sent.', [
                'phone' => $phoneNumber,
                'message' => $message
            ]);
            return false;
        }

        // Normalize phone number (remove spaces, dashes, etc.)
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);

        try {
            $response = Http::asForm()->post("{$this->baseUrl}/messages", [
                'apikey' => $this->apiKey,
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('SMS sent successfully', [
                    'phone' => $phoneNumber,
                    'message_id' => $data['message_id'] ?? null,
                ]);

                return true;
            } else {
                Log::error('Failed to send SMS', [
                    'phone' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    public function sendBulk(array $phoneNumbers, string $message)
    {
        if (!$this->apiKey) {
            Log::warning('Semaphore API key not configured. Bulk SMS not sent.');
            return false;
        }

        $phoneNumbers = array_map([$this, 'normalizePhoneNumber'], $phoneNumbers);

        try {
            $response = Http::asForm()->post("{$this->baseUrl}/messages", [
                'apikey' => $this->apiKey,
                'number' => implode(',', $phoneNumbers),
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                Log::info('Bulk SMS sent successfully', [
                    'count' => count($phoneNumbers),
                ]);

                return true;
            } else {
                Log::error('Failed to send bulk SMS', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending bulk SMS', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getAccountBalance()
    {
        if (!$this->apiKey) {
            return null;
        }

        try {
            $response = Http::get("{$this->baseUrl}/account", [
                'apikey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['credit_balance'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get account balance', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function normalizePhoneNumber(string $phoneNumber)
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

    public function validatePhoneNumber(string $phoneNumber)
    {
        $normalized = $this->normalizePhoneNumber($phoneNumber);
        
        // Philippine mobile numbers should be 12 digits starting with 63
        return strlen($normalized) === 12 && substr($normalized, 0, 2) === '63';
    }
}