<?php

namespace App\Console\Commands;

use App\Services\Channels\SmsChannelFactory;
use App\Services\AndroidSmsService;
use Illuminate\Console\Command;

class TestSmsCommand extends Command
{
    protected $signature = 'sms:test {phone} {--message=}';
    protected $description = 'Test SMS sending via configured gateway';

    protected $channelFactory;
    protected $androidService;

    public function __construct(SmsChannelFactory $channelFactory, AndroidSmsService $androidService)
    {
        parent::__construct();
        $this->channelFactory = $channelFactory;
        $this->androidService = $androidService;
    }

    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->option('message') ?? 'Test SMS from CAFA Hardware POS';

        $this->info('=== SMS Gateway Test ===');
        $this->newLine();

        // Check gateway status
        $this->info('📡 Checking SMS-Gate.app status...');
        $status = $this->androidService->getStatus();
        
        if ($status['online']) {
            $this->info('✅ Gateway: ONLINE');
            $this->info('   Type: ' . ($status['type'] ?? 'unknown'));
            if (isset($status['device_id'])) {
                $this->info('   Device ID: ' . $status['device_id']);
            }
        } else {
            $this->warn('⚠️  Gateway: OFFLINE');
            $this->warn('   Message: ' . ($status['message'] ?? 'Unknown error'));
        }
        $this->newLine();

        // Get channel status
        $channelsStatus = $this->channelFactory->getChannelsStatus();
        $this->info('📊 Channel Status:');
        $this->info('   Primary Provider: ' . $channelsStatus['primary_provider']);
        $this->info('   Fallback Enabled: ' . ($channelsStatus['fallback_enabled'] ? 'Yes' : 'No'));
        
        if ($channelsStatus['android_gateway']['available']) {
            $this->info('   ✅ Android Gateway: Available');
        } else {
            $this->warn('   ❌ Android Gateway: Unavailable');
        }
        
        if ($channelsStatus['semaphore']['available']) {
            $this->info('   ✅ Semaphore: Available (fallback)');
        } else {
            $this->warn('   ⚠️  Semaphore: Not configured');
        }
        $this->newLine();

        // Send test SMS
        if ($this->confirm('Send test SMS to ' . $phone . '?', true)) {
            $this->info('📤 Sending SMS...');
            
            $result = $this->channelFactory->send($phone, $message);
            
            if ($result) {
                $this->info('✅ SMS sent successfully!');
                $this->info('   Phone: ' . $phone);
                $this->info('   Message: ' . $message);
                $this->newLine();
                $this->info('📱 Check your phone for the SMS!');
            } else {
                $this->error('❌ Failed to send SMS');
                $this->error('   Check Laravel logs for details: storage/logs/laravel.log');
            }
        } else {
            $this->info('SMS sending cancelled.');
        }

        return 0;
    }
}
