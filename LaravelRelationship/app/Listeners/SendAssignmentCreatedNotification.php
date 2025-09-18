<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignmentCreatedNotification implements ShouldQueue
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
    public function handle(AssignmentCreated $event): void
    {
        $assignment = $event->assignment;
        
        // Get all students enrolled in the subject
        $students = $assignment->subject->students;
        
        // Send notification to each student
        foreach ($students as $student) {
            $student->notify(new AssignmentCreatedNotification($assignment));
        }
    }
}
