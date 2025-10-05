<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;
use App\Models\Student;
use App\Http\Resources\AssignmentResource;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = Student::where('email', $user->email)->firstOrFail();

        $assignments = Assignment::whereHas('subject.students', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->with(['subject', 'teacher', 'submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->orderBy('due_date')
            ->get();

        return AssignmentResource::collection($assignments);
    }
}



