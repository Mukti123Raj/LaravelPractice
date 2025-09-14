<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AssignmentCreatedNotification;
use App\Notifications\AssignmentSubmittedNotification;
use App\Notifications\AssignmentGradedNotification;

class AssignmentController extends Controller
{
    public function index($subjectId)
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        $subject = Subject::with(['assignments.submissions.student', 'students'])
            ->where('id', $subjectId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        return view('teacher.subjects', compact('subject', 'teacher'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'total_marks' => 'required|integer|min:1',
            'due_date' => 'required|date|after:now',
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        // Verify the subject belongs to this teacher
        $subject = Subject::where('id', $request->subject_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $assignment = Assignment::create([
            'title' => $request->title,
            'description' => $request->description,
            'instructions' => $request->instructions,
            'total_marks' => $request->total_marks,
            'due_date' => $request->due_date,
            'subject_id' => $request->subject_id,
            'teacher_id' => $teacher->id
        ]);

        // Send notification to all students enrolled in this subject
        $students = $subject->students;
        foreach ($students as $student) {
            $user = User::where('email', $student->email)->first();
            if ($user) {
                $user->notify(new AssignmentCreatedNotification($assignment));
            }
        }

        return redirect()->route('teacher.subjects.show', $subject->id)
            ->with('success', 'Assignment created successfully!');
    }

    public function show($assignmentId)
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        $assignment = Assignment::with(['subject', 'submissions.student'])
            ->where('id', $assignmentId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        // Get all students enrolled in this subject
        $enrolledStudents = $assignment->subject->students;
        
        // Get submission status for each student
        $studentSubmissions = [];
        foreach ($enrolledStudents as $student) {
            $submission = $assignment->submissions->where('student_id', $student->id)->first();
            $studentSubmissions[] = [
                'student' => $student,
                'submission' => $submission,
                'status' => $submission ? $submission->status : 'not_submitted'
            ];
        }

        return view('teacher.assignment-details', compact('assignment', 'studentSubmissions'));
    }

    public function gradeSubmission(Request $request, $submissionId)
    {
        $request->validate([
            'marks_obtained' => 'required|integer|min:0',
            'teacher_feedback' => 'nullable|string'
        ]);

        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        $submission = AssignmentSubmission::with('assignment')
            ->where('id', $submissionId)
            ->whereHas('assignment', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->firstOrFail();

        // Validate marks don't exceed total marks
        if ($request->marks_obtained > $submission->assignment->total_marks) {
            return back()->withErrors(['marks_obtained' => 'Marks cannot exceed total marks.']);
        }

        $submission->update([
            'marks_obtained' => $request->marks_obtained,
            'teacher_feedback' => $request->teacher_feedback,
            'graded_at' => now()
        ]);

        // Send notification to the student
        $student = $submission->student;
        $user = User::where('email', $student->email)->first();
        if ($user) {
            $user->notify(new AssignmentGradedNotification($submission));
        }

        return back()->with('success', 'Assignment graded successfully!');
    }

    public function delete($assignmentId)
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        $assignment = Assignment::where('id', $assignmentId)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $subjectId = $assignment->subject_id;
        $assignment->delete();

        return redirect()->route('teacher.subjects.show', $subjectId)
            ->with('success', 'Assignment deleted successfully!');
    }
}