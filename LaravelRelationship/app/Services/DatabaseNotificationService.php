<?php

namespace App\Services;

use App\Interfaces\NotificationService;
use App\Models\Notification;

class DatabaseNotificationService implements NotificationService
{
    public function send(string $message, int $userId): bool
    {
        try {
            Notification::create([
                'message' => $message,
                'user_id' => $userId,
            ]);

            return true;
        } catch (\Exception $e) {
            // Log the exception
            return false;
        }
    }
}


