<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Assignment;

class AssignmentCreated
{
    use Dispatchable, SerializesModels;

    public $assignment;

    /**
     * Create a new event instance.
     */
    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }
}
