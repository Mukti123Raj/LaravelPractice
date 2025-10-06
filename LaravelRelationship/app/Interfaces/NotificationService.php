<?php

namespace App\Interfaces;

interface NotificationService
{
    public function send(string $message, int $userId): bool;
}


