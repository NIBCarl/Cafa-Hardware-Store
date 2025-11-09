<?php

declare(strict_types=1);

namespace App\Services\Channels;

use App\Services\AndroidSmsService;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;

class SmsChannelFactory
{
    protected $androidChannel;
    protected $semaphoreChannel;
    protected $provider;
    protected $fallbackEnabled;

    public function __construct(
        AndroidSmsService $androidService,
        SmsService $smsService
    ) {
        $this->androidChannel = new AndroidSmsChannel($androidService);
        $this->semaphoreChannel = new SemaphoreSmsChannel($smsService);
        $this->provider = config('services.sms.provider', 'semaphore');
        $this->fallbackEnabled = config('services.sms.fallback_enabled', true);
    }

    /**
     * Get the primary SMS channel based on configuration
     *
     * @return SmsChannelInterface
     */
    public function getPrimaryChannel(): SmsChannelInterface
    {
        return match ($this->provider) {
            'android', 'android_gateway' => $this->androidChannel,
            'semaphore' => $this->semaphoreChannel,
            'hybrid' => $this->getHybridChannel(),
            default => $this->semaphoreChannel,
        };
    }

    /**
     * Get fallback channel
     *
     * @return SmsChannelInterface|null
     */
    public function getFallbackChannel(): ?SmsChannelInterface
    {
        if (!$this->fallbackEnabled) {
            return null;
        }

        // If primary is Android, fallback to Semaphore
        if (in_array($this->provider, ['android', 'android_gateway', 'hybrid'])) {
            return $this->semaphoreChannel->isAvailable() ? $this->semaphoreChannel : null;
        }

        // If primary is Semaphore, no fallback needed (it's cloud-based)
        return null;
    }

    /**
     * Send SMS with automatic fallback
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public function send(string $phoneNumber, string $message): bool
    {
        $primary = $this->getPrimaryChannel();

        Log::info('Attempting to send SMS', [
            'primary_channel' => $primary->getChannelName(),
            'phone' => $phoneNumber,
        ]);

        // Try primary channel
        if ($primary->isAvailable()) {
            $result = $primary->send($phoneNumber, $message);
            
            if ($result) {
                Log::info('SMS sent successfully via primary channel', [
                    'channel' => $primary->getChannelName(),
                ]);
                return true;
            }

            Log::warning('Primary SMS channel failed', [
                'channel' => $primary->getChannelName(),
            ]);
        } else {
            Log::warning('Primary SMS channel not available', [
                'channel' => $primary->getChannelName(),
            ]);
        }

        // Try fallback channel
        $fallback = $this->getFallbackChannel();
        
        if ($fallback) {
            Log::info('Attempting fallback SMS channel', [
                'channel' => $fallback->getChannelName(),
            ]);

            if ($fallback->isAvailable()) {
                $result = $fallback->send($phoneNumber, $message);
                
                if ($result) {
                    Log::info('SMS sent successfully via fallback channel', [
                        'channel' => $fallback->getChannelName(),
                    ]);
                    return true;
                }
            }

            Log::error('Fallback SMS channel also failed', [
                'channel' => $fallback->getChannelName(),
            ]);
        }

        Log::error('All SMS channels failed', [
            'phone' => $phoneNumber,
        ]);

        return false;
    }

    /**
     * Send bulk SMS with automatic fallback
     *
     * @param array $phoneNumbers
     * @param string $message
     * @return bool
     */
    public function sendBulk(array $phoneNumbers, string $message): bool
    {
        $primary = $this->getPrimaryChannel();

        // Try primary channel
        if ($primary->isAvailable()) {
            $result = $primary->sendBulk($phoneNumbers, $message);
            
            if ($result) {
                return true;
            }

            Log::warning('Primary bulk SMS channel failed', [
                'channel' => $primary->getChannelName(),
            ]);
        }

        // Try fallback channel
        $fallback = $this->getFallbackChannel();
        
        if ($fallback && $fallback->isAvailable()) {
            Log::info('Attempting bulk SMS via fallback channel', [
                'channel' => $fallback->getChannelName(),
            ]);

            return $fallback->sendBulk($phoneNumbers, $message);
        }

        return false;
    }

    /**
     * Get channel for hybrid mode (prefers Android, falls back to Semaphore)
     *
     * @return SmsChannelInterface
     */
    protected function getHybridChannel(): SmsChannelInterface
    {
        if ($this->androidChannel->isAvailable()) {
            return $this->androidChannel;
        }

        return $this->semaphoreChannel;
    }

    /**
     * Get all available channels
     *
     * @return array
     */
    public function getAvailableChannels(): array
    {
        $channels = [];

        if ($this->androidChannel->isAvailable()) {
            $channels[] = 'android_gateway';
        }

        if ($this->semaphoreChannel->isAvailable()) {
            $channels[] = 'semaphore';
        }

        return $channels;
    }

    /**
     * Get status of all channels
     *
     * @return array
     */
    public function getChannelsStatus(): array
    {
        return [
            'primary_provider' => $this->provider,
            'fallback_enabled' => $this->fallbackEnabled,
            'android_gateway' => [
                'available' => $this->androidChannel->isAvailable(),
                'name' => 'Android SMS Gateway',
            ],
            'semaphore' => [
                'available' => $this->semaphoreChannel->isAvailable(),
                'name' => 'Semaphore SMS API',
            ],
        ];
    }
}
