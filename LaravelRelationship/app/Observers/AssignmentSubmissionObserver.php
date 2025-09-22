<?php

namespace App\Observers;

use App\Events\AssignmentGraded;
use App\Events\AssignmentSubmitted;
use App\Models\AssignmentSubmission;

class AssignmentSubmissionObserver
{
    /**
     * Handle the AssignmentSubmission "created" event.
     */
    public function created(AssignmentSubmission $submission): void
    {
        event(new AssignmentSubmitted($submission));
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


