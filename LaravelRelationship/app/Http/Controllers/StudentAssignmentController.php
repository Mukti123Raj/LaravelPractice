<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AssignmentSubmittedNotification;

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
                $status = $assignment->getStatusForStudent($student->id);
                $assignments->push([
                    'assignment' => $assignment,
                    'submission' => $submission,
                    'status' => $status,
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
        $status = $assignment->getStatusForStudent($student->id);

        return view('student.assignment-details', compact('assignment', 'submission', 'student', 'status'));
    }

    public function submit(Request $request, $assignmentId)
    {
        $request->validate([
            'submission_content' => 'nullable|string',
            'submission_file' => 'nullable|file|mimes:pdf,doc,docx|max:1024'
        ]);

        // Check if at least one submission method is provided
        if (empty($request->submission_content) && !$request->hasFile('submission_file')) {
            return back()->withErrors(['submission' => 'Please provide either text content or upload a file.']);
        }

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

        $filePath = null;
        if ($request->hasFile('submission_file')) {
            $file = $request->file('submission_file');
            $fileName = time() . '_' . $student->id . '_' . $assignmentId . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('assignment_submissions', $fileName, 'public');
        }

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignmentId,
            'student_id' => $student->id,
            'submission_content' => $request->submission_content,
            'file_path' => $filePath,
            'submitted_at' => now()
        ]);

        // Send notification to the teacher
        $teacher = $assignment->subject->teacher;
        $teacherUser = User::where('email', $teacher->email)->first();
        if ($teacherUser) {
            $teacherUser->notify(new AssignmentSubmittedNotification($submission));
        }

        return redirect()->route('student.assignments')
            ->with('success', 'Assignment submitted successfully!');
    }

    public function updateSubmission(Request $request, $assignmentId)
    {
        $request->validate([
            'submission_content' => 'nullable|string',
            'submission_file' => 'nullable|file|mimes:pdf,doc,docx|max:1024'
        ]);

        // Check if at least one submission method is provided
        if (empty($request->submission_content) && !$request->hasFile('submission_file')) {
            return back()->withErrors(['submission' => 'Please provide either text content or upload a file.']);
        }

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

        $filePath = $submission->file_path;
        if ($request->hasFile('submission_file')) {
            // Delete old file if exists
            if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                Storage::disk('public')->delete($submission->file_path);
            }
            
            // Store new file
            $file = $request->file('submission_file');
            $fileName = time() . '_' . $student->id . '_' . $assignmentId . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('assignment_submissions', $fileName, 'public');
        }

        $submission->update([
            'submission_content' => $request->submission_content,
            'file_path' => $filePath,
            'submitted_at' => now()
        ]);

        return redirect()->route('student.assignments')
            ->with('success', 'Assignment updated successfully!');
    }

    public function download($submissionId)
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $submission = AssignmentSubmission::where('id', $submissionId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            return back()->withErrors(['error' => 'File not found.']);
        }

        return Storage::disk('public')->download($submission->file_path, basename($submission->file_path));
    }
}