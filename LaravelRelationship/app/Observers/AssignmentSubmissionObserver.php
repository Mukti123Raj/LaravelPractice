<?php

namespace App\Observers;

use App\Events\AssignmentGraded;
use App\Events\AssignmentSubmitted;
use App\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Cache;

class AssignmentSubmissionObserver
{
    /**
     * Handle the AssignmentSubmission "created" event.
     */
    public function created(AssignmentSubmission $submission): void
    {
        event(new AssignmentSubmitted($submission));
        
        // Clear specific cache keys for this student
        Cache::forget("student:{$submission->student_id}:dashboard");
        Cache::forget("student:{$submission->student_id}:assignments:index");
        Cache::forget("student:{$submission->student_id}:subjects");
    }

    /**
     * Handle the AssignmentSubmission "updated" event.
     */
    public function updated(AssignmentSubmission $submission): void
    {
        if ($submission->wasChanged('marks_obtained') && $submission->marks_obtained !== null) {
            event(new AssignmentGraded($submission));
        }
    }
}


