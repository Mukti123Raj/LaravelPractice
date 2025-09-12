<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;

class StudentAssignmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::with(['subjects.assignments.submissions' => function ($query) use ($user) {
            $query->where('student_id', $user->id);
        }])
            ->where('email', $user->email)
            ->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        // Get all assignments from enrolled subjects
        $assignments = collect();
        foreach ($student->subjects as $subject) {
            foreach ($subject->assignments as $assignment) {
                $submission = $assignment->submissions->first();
                $assignments->push([
                    'assignment' => $assignment,
                    'submission' => $submission,
                    'status' => $submission ? $submission->status : 'not_submitted',
                    'subject' => $subject
                ]);
            }
        }

        // Sort by due date
        $assignments = $assignments->sortBy('assignment.due_date');

        return view('student.assignments', compact('assignments', 'student'));
    }

    public function show($assignmentId)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $assignment = Assignment::with(['subject', 'submissions' => function ($query) use ($student) {
            $query->where('student_id', $student->id);
        }])
            ->where('id', $assignmentId)
            ->whereHas('subject.students', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->firstOrFail();

        $submission = $assignment->submissions->first();

        return view('student.assignment-details', compact('assignment', 'submission', 'student'));
    }

    public function submit(Request $request, $assignmentId)
    {
        $request->validate([
            'submission_content' => 'required|string'
        ]);

        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $assignment = Assignment::where('id', $assignmentId)
            ->whereHas('subject.students', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->firstOrFail();

        // Check if assignment is still open
        if (now()->gt($assignment->due_date)) {
            return back()->withErrors(['submission' => 'Assignment deadline has passed.']);
        }

        // Check if already submitted
        $existingSubmission = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return back()->withErrors(['submission' => 'You have already submitted this assignment.']);
        }

        AssignmentSubmission::create([
            'assignment_id' => $assignmentId,
            'student_id' => $student->id,
            'submission_content' => $request->submission_content,
            'submitted_at' => now()
        ]);

        return redirect()->route('student.assignments')
            ->with('success', 'Assignment submitted successfully!');
    }

    public function updateSubmission(Request $request, $assignmentId)
    {
        $request->validate([
            'submission_content' => 'required|string'
        ]);

        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $assignment = Assignment::where('id', $assignmentId)
            ->whereHas('subject.students', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->firstOrFail();

        // Check if assignment is still open
        if (now()->gt($assignment->due_date)) {
            return back()->withErrors(['submission' => 'Assignment deadline has passed.']);
        }

        $submission = AssignmentSubmission::where('assignment_id', $assignmentId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Check if already graded
        if ($submission->graded_at) {
            return back()->withErrors(['submission' => 'Cannot update submission after grading.']);
        }

        $submission->update([
            'submission_content' => $request->submission_content,
            'submitted_at' => now()
        ]);

        return redirect()->route('student.assignments')
            ->with('success', 'Assignment updated successfully!');
    }
}