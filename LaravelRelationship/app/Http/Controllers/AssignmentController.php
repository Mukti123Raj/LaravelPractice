<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
    protected $teacher;

    public function __construct()
    {
        $this->teacher = \App\Models\Teacher::where('email', Auth::user()->email ?? null)->first();
    }
    public function index($subjectId)
    {
        try {
            $teacher = $this->teacher;

            if (!$teacher) {
                return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
            }

            $cacheKey = "teacher:{$teacher->id}:subject:{$subjectId}";
            $subject = Cache::remember($cacheKey, 30, function () use ($subjectId, $teacher) {
                return Subject::with(['assignments.submissions.student', 'students'])
                    ->where('id', $subjectId)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();
            });

            return view('teacher.subjects', compact('subject', 'teacher'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Subject not found or you do not have permission to access it.']);
        } catch (\Exception $e) {
            \Log::error('Error accessing subject: ' . $e->getMessage(), [
                'subject_id' => $subjectId,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'An error occurred while loading the subject. Please try again.']);
        }
    }

    public function create(\App\Http\Requests\StoreAssignmentRequest $request)
    {
        try {
            // Validation handled by StoreAssignmentRequest
            $teacher = $this->teacher;

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

            try {
                $students = $subject->students;
                foreach ($students as $student) {
                    $user = User::where('email', $student->email)->first();
                    if ($user) {
                        $user->notify(new AssignmentCreatedNotification($assignment));
                    }
                }
            } catch (\Exception $notificationError) {
                \Log::warning('Failed to send assignment notifications: ' . $notificationError->getMessage(), [
                    'assignment_id' => $assignment->id,
                    'subject_id' => $subject->id
                ]);
            }

            return redirect()->route('teacher.subjects.show', $subject->id)
                ->with('success', 'Assignment created successfully!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Subject not found or you do not have permission to create assignments for it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating assignment: ' . $e->getMessage(), [
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to create assignment. Please check your input and try again.'])
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error creating assignment: ' . $e->getMessage(), [
                'subject_id' => $request->subject_id,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred while creating the assignment. Please try again.'])
                ->withInput();
        }
    }

    public function show($assignmentId)
    {
        try {
            $teacher = $this->teacher;

            if (!$teacher) {
                return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
            }

            $assignment = Cache::remember("teacher:{$teacher->id}:assignment:{$assignmentId}", 30, function () use ($assignmentId, $teacher) {
                return Assignment::with(['subject', 'submissions.student'])
                    ->where('id', $assignmentId)
                    ->where('teacher_id', $teacher->id)
                    ->firstOrFail();
            });

            $enrolledStudents = $assignment->subject->students;
            
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Assignment not found or you do not have permission to access it.']);
        } catch (\Exception $e) {
            \Log::error('Error accessing assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'An error occurred while loading the assignment. Please try again.']);
        }
    }

    public function gradeSubmission(Request $request, $submissionId)
    {
        try {
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

            if ($request->marks_obtained > $submission->assignment->total_marks) {
                return back()->withErrors(['marks_obtained' => 'Marks cannot exceed total marks.']);
            }

            $submission->update([
                'marks_obtained' => $request->marks_obtained,
                'teacher_feedback' => $request->teacher_feedback,
                'graded_at' => now()
            ]);

            try {
                $student = $submission->student;
                $user = User::where('email', $student->email)->first();
                if ($user) {
                    $user->notify(new AssignmentGradedNotification($submission));
                }
            } catch (\Exception $notificationError) {
                \Log::warning('Failed to send grading notification: ' . $notificationError->getMessage(), [
                    'submission_id' => $submissionId,
                    'student_id' => $submission->student_id
                ]);
            }

            return back()->with('success', 'Assignment graded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Submission not found or you do not have permission to grade it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error grading submission: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to grade assignment. Please try again.']);
        } catch (\Exception $e) {
            \Log::error('Error grading submission: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred while grading the assignment. Please try again.']);
        }
    }

    public function delete($assignmentId)
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Assignment not found or you do not have permission to delete it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error deleting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to delete assignment. Please try again.']);
        } catch (\Exception $e) {
            \Log::error('Error deleting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred while deleting the assignment. Please try again.']);
        }
    }
}