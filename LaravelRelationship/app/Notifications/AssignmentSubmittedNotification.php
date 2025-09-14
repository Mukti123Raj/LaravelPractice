<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\AssignmentSubmission;

class AssignmentSubmittedNotification extends Notification
{
    use Queueable;

    protected $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssignmentSubmission $submission)
    {
        $this->submission = $submission;
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
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment->title,
            'subject_name' => $this->submission->assignment->subject->name,
            'student_name' => $this->submission->student->name,
            'submitted_at' => $this->submission->created_at->format('M d, Y H:i'),
            'message' => "{$this->submission->student->name} submitted assignment '{$this->submission->assignment->title}' for {$this->submission->assignment->subject->name}",
            'type' => 'assignment_submitted'
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
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment->title,
            'subject_name' => $this->submission->assignment->subject->name,
            'student_name' => $this->submission->student->name,
            'submitted_at' => $this->submission->created_at->format('M d, Y H:i'),
            'message' => "{$this->submission->student->name} submitted assignment '{$this->submission->assignment->title}' for {$this->submission->assignment->subject->name}",
            'type' => 'assignment_submitted'
        ];
    }
}
