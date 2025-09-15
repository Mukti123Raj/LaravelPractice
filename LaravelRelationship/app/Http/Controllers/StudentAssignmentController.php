<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AssignmentSubmittedNotification;

class StudentAssignmentController extends Controller
{
    protected $student;

    public function __construct()
    {
        $user = Auth::user();
        $this->student = $user
            ? Student::where('email', $user->email)->first()
            : null;
    }

    public function index()
    {
        if (!$this->student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $student = $this->student;
        $student = Cache::remember("student:{$student->id}:assignments:index", 30, function () use ($student) {
            return Student::with(['subjects.assignments.submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
                ->where('id', $student->id)
                ->first();
        });

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
        if (!$this->student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $student = $this->student;
        $assignment = Cache::remember("student:{$student->id}:assignment:{$assignmentId}", 30, function () use ($assignmentId, $student) {
            return Assignment::with(['subject', 'submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
                ->where('id', $assignmentId)
                ->whereHas('subject.students', function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                })
                ->firstOrFail();
        });

        $submission = $assignment->submissions->first();
        $status = $assignment->getStatusForStudent($student->id);

        return view('student.assignment-details', compact('assignment', 'submission', 'student', 'status'));
    }

    public function submit(\App\Http\Requests\Student\StudentSubmitAssignmentRequest $request, $assignmentId)
    {
        // Validation handled by StudentSubmitAssignmentRequest

        if (!$this->student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $student = $this->student;
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

    public function updateSubmission(\App\Http\Requests\Student\StudentUpdateAssignmentRequest $request, $assignmentId)
    {
        // Validation handled by StudentUpdateAssignmentRequest

        if (!$this->student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $student = $this->student;
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