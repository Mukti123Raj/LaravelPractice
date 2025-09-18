<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\AssignmentSubmission;

class AssignmentSubmitted
{
    use Dispatchable, SerializesModels;

    public $submission;

    /**
     * Create a new event instance.
     */
    public function __construct(AssignmentSubmission $submission)
    {
        $this->submission = $submission;
    }
}
