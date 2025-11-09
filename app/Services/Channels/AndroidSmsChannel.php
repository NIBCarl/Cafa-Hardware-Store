<?php

declare(strict_types=1);

namespace App\Services\Channels;

use App\Services\AndroidSmsService;

class AndroidSmsChannel implements SmsChannelInterface
{
    protected $androidService;

    public function __construct(AndroidSmsService $androidService)
    {
        $this->androidService = $androidService;
    }

    public function send(string $phoneNumber, string $message): bool
    {
        return $this->androidService->send($phoneNumber, $message);
    }

    public function sendBulk(array $phoneNumbers, string $message): bool
    {
        return $this->androidService->sendBulk($phoneNumbers, $message);
    }

    public function isAvailable(): bool
    {
        return $this->androidService->isOnline();
    }

    public function getChannelName(): string
    {
        return 'android_gateway';
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return $this->androidService->validatePhoneNumber($phoneNumber);
    }
}
