<?php

namespace App\Services;

use App\Models\TontineNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to a user.
     */
    public function notify(string $userId, string $type, string $message, array $metadata = [], string $channel = 'sms')
    {
        Log::info("Notification ($type) for user $userId: $message");

        $notification = TontineNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'metadata' => $metadata,
            'channel' => $channel,
            'is_sent' => false,
        ]);

        // Trigger external SMS API if channel is SMS
        if ($channel === 'sms') {
            $this->sendSms($userId, $message);
            $notification->update(['is_sent' => true, 'sent_at' => now()]);
        }

        return $notification;
    }

    protected function sendSms($userId, $message)
    {
        // Mocking SMS sending logic
        Log::debug("SMS Sent to User $userId: $message");
    }
}
