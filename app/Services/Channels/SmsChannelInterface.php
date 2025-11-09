<?php

declare(strict_types=1);

namespace App\Services\Channels;

interface SmsChannelInterface
{
    /**
     * Send SMS to a single recipient
     *
     * @param string $phoneNumber The recipient's phone number
     * @param string $message The message content
     * @return bool Success status
     */
    public function send(string $phoneNumber, string $message): bool;

    /**
     * Send SMS to multiple recipients
     *
     * @param array $phoneNumbers Array of phone numbers
     * @param string $message The message content
     * @return bool Success status
     */
    public function sendBulk(array $phoneNumbers, string $message): bool;

    /**
     * Check if the channel is currently available
     *
     * @return bool Availability status
     */
    public function isAvailable(): bool;

    /**
     * Get the channel name
     *
     * @return string Channel identifier
     */
    public function getChannelName(): string;

    /**
     * Validate phone number format
     *
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool;
}
