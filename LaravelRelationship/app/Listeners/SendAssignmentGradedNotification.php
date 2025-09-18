<?php

namespace App\Listeners;

use App\Events\AssignmentGraded;
use App\Notifications\AssignmentGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignmentGradedNotification implements ShouldQueue
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
    public function handle(AssignmentGraded $event): void
    {
        $submission = $event->submission;
        
        // Send notification to the student who submitted the assignment
        $submission->student->notify(new AssignmentGradedNotification($submission));
    }
}
