<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Student;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $student = $user ? Student::where('email', $user->email)->first() : null;
        $studentId = $student ? $student->id : null;

        $assignment = $this->resource;

        return [
            'id' => $assignment->id,
            'title' => $assignment->title,
            'description' => $assignment->description,
            'due_date' => $assignment->due_date ? $assignment->due_date->toIso8601String() : null,
            'subject_name' => optional($assignment->subject)->name,
            'teacher_name' => optional($assignment->teacher)->name,
            'status' => $studentId ? $assignment->getStatusForStudent($studentId) : null,
        ];
    }
}



