<?php

declare(strict_types=1);

namespace App\Services\Channels;

use App\Services\SmsService;

class SemaphoreSmsChannel implements SmsChannelInterface
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send(string $phoneNumber, string $message): bool
    {
        return $this->smsService->send($phoneNumber, $message);
    }

    public function sendBulk(array $phoneNumbers, string $message): bool
    {
        return $this->smsService->sendBulk($phoneNumbers, $message);
    }

    public function isAvailable(): bool
    {
        // Semaphore is available if API key is configured
        return !empty(config('services.semaphore.api_key'));
    }

    public function getChannelName(): string
    {
        return 'semaphore';
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return $this->smsService->validatePhoneNumber($phoneNumber);
    }
}
