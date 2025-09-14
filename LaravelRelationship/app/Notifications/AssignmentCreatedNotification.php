<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\Assignment;

class AssignmentCreatedNotification extends Notification
{
    use Queueable;

    protected $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'subject_name' => $this->assignment->subject->name,
            'teacher_name' => $this->assignment->subject->teacher->name,
            'due_date' => $this->assignment->due_date->format('M d, Y H:i'),
            'total_marks' => $this->assignment->total_marks,
            'message' => "New assignment '{$this->assignment->title}' has been created for {$this->assignment->subject->name}",
            'type' => 'assignment_created'
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'assignment_title' => $this->assignment->title,
            'subject_name' => $this->assignment->subject->name,
            'teacher_name' => $this->assignment->subject->teacher->name,
            'due_date' => $this->assignment->due_date->format('M d, Y H:i'),
            'total_marks' => $this->assignment->total_marks,
            'message' => "New assignment '{$this->assignment->title}' has been created for {$this->assignment->subject->name}",
            'type' => 'assignment_created'
        ];
    }
}
