<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Support\Facades\Log;

class StudentObserver
{

    public function created(Student $student): void
    {
        Log::info("New student created: {$student->name}");
    }

    public function updated(Student $student): void
    {
        Log::info("Student updated: {$student->name}");
    }
}
