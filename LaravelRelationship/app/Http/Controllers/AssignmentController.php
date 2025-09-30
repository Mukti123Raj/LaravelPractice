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
use App\Services\AssignmentService;

class AssignmentController extends Controller
{
    protected $teacher;
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->teacher = \App\Models\Teacher::where('email', Auth::user()->email ?? null)->first();
        $this->assignmentService = $assignmentService;
    }
    public function index($subjectId)
    {
        try {
            $teacher = $this->teacher;

            if (!$teacher) {
                return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
            }

            $subjects = $this->assignmentService->getTeacherAssignments(Auth::user());
            $subject = $subjects->firstWhere('id', (int) $subjectId);
            if (!$subject) {
                abort(404);
            }

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
            $validated = $request->validated();
            $validated['user'] = Auth::user();
            $assignment = $this->assignmentService->createAssignment($validated);

            return redirect()->route('teacher.subjects.show', $assignment->subject_id)
                ->with('success', 'Assignment created successfully!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Subject not found or you do not have permission to create assignments for it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating assignment: ' . $e->getMessage(), [
                'subject_id' => $request->subject_id,
                'teacher_id' => $this->teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to create assignment. Please check your input and try again.'])
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error creating assignment: ' . $e->getMessage(), [
                'subject_id' => $request->subject_id,
                'teacher_id' => $this->teacher->id ?? null,
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

            $submission = AssignmentSubmission::with('assignment')->findOrFail($submissionId);

            $this->assignmentService->gradeSubmission(
                $submission,
                (int) $request->marks_obtained,
                $request->teacher_feedback
            );

            return back()->with('success', 'Assignment graded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Submission not found or you do not have permission to grade it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error grading submission: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'teacher_id' => $this->teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to grade assignment. Please try again.']);
        } catch (\Exception $e) {
            \Log::error('Error grading submission: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'teacher_id' => $this->teacher->id ?? null,
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