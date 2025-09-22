<?php

namespace App\Observers;

use App\Events\AssignmentCreated;
use App\Models\Assignment;

class AssignmentObserver
{
    /**
     * Handle the Assignment "created" event.
     */
    public function created(Assignment $assignment): void
    {
        event(new AssignmentCreated($assignment));
    }
}


