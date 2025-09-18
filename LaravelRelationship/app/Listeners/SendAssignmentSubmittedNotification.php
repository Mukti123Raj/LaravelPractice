<?php

namespace App\Listeners;

use App\Events\AssignmentSubmitted;
use App\Notifications\AssignmentSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignmentSubmittedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AssignmentSubmitted $event): void
    {
        $submission = $event->submission;
        
        // Send notification to the teacher who created the assignment
        $submission->assignment->teacher->notify(new AssignmentSubmittedNotification($submission));
    }
}
