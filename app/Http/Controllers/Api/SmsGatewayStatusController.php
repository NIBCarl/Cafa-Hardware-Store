<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AndroidSmsService;
use App\Services\SmsService;
use App\Services\Channels\SmsChannelFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsGatewayStatusController extends Controller
{
    protected $androidSmsService;
    protected $smsService;
    protected $channelFactory;

    public function __construct(
        AndroidSmsService $androidSmsService,
        SmsService $smsService,
        SmsChannelFactory $channelFactory
    ) {
        $this->androidSmsService = $androidSmsService;
        $this->smsService = $smsService;
        $this->channelFactory = $channelFactory;
    }

    /**
     * Get status of all SMS channels
     *
     * @return JsonResponse
     */
    public function getStatus(): JsonResponse
    {
        $androidStatus = $this->androidSmsService->getStatus();
        $channelsStatus = $this->channelFactory->getChannelsStatus();

        return response()->json([
            'success' => true,
            'data' => [
                'android_gateway' => $androidStatus,
                'channels' => $channelsStatus,
                'semaphore_balance' => $this->smsService->getAccountBalance(),
            ],
        ]);
    }

    /**
     * Test SMS sending via specified channel
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function testSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string|max:160',
            'channel' => 'nullable|string|in:android,semaphore,auto',
        ]);

        $phone = $validated['phone'];
        $message = $validated['message'] ?? 'Test message from CAFA Hardware POS';
        $channel = $validated['channel'] ?? 'auto';

        try {
            $result = false;

            if ($channel === 'android') {
                $result = $this->androidSmsService->send($phone, $message);
            } elseif ($channel === 'semaphore') {
                $result = $this->smsService->send($phone, $message);
            } else {
                // Use factory (auto-selection with fallback)
                $result = $this->channelFactory->send($phone, $message);
            }

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test SMS sent successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test SMS. Check logs for details.',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Test SMS failed', [
                'error' => $e->getMessage(),
                'phone' => $phone,
                'channel' => $channel,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending test SMS: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ping Android gateway to check connectivity
     *
     * @return JsonResponse
     */
    public function pingAndroidGateway(): JsonResponse
    {
        $isOnline = $this->androidSmsService->isOnline();

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'message' => $isOnline 
                ? 'Android gateway is online' 
                : 'Android gateway is offline or unreachable',
        ]);
    }
}
