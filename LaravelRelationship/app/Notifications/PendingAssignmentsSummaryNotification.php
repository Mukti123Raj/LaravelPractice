<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingAssignmentsSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $pendingCount,
        private int $overdueCount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $total = $this->pendingCount + $this->overdueCount;

        return [
            'type' => 'assignments_summary',
            'message' => $total > 0
                ? "You have {$this->pendingCount} pending and {$this->overdueCount} overdue assignments."
                : 'You have no pending assignments.',
            'pending' => $this->pendingCount,
            'overdue' => $this->overdueCount,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Optional: if later you add 'mail' to via(), this will be used
        return (new MailMessage)
            ->subject('Daily Assignments Summary')
            ->line('Here is your daily assignments summary:')
            ->line("Pending: {$this->pendingCount}")
            ->line("Overdue: {$this->overdueCount}");
    }
}


