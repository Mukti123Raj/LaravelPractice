<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\NotificationService;
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
    protected $notificationService;

    public function __construct(AssignmentService $assignmentService, NotificationService $notificationService)
    {
        $this->teacher = \App\Models\Teacher::where('email', Auth::user()->email ?? null)->first();
        $this->assignmentService = $assignmentService;
        $this->notificationService = $notificationService;
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
            $this->authorize('create', Assignment::class);
            
            $validated = $request->validated();
            $validated['user'] = Auth::user();
            $assignment = $this->assignmentService->createAssignment($validated);

            // Send a notification using the service
            $this->notificationService->send('A new assignment has been created!', Auth::id());

            return redirect()->route('teacher.subjects.show', $assignment->subject_id)
                ->with('success', 'Assignment created successfully!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'You do not have permission to create assignments.']);
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

    public function show(Assignment $assignment)
    {
        try {
            $this->authorize('view', $assignment);

            $teacher = $this->teacher;

            if (!$teacher) {
                return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
            }

            $assignment = Cache::remember("teacher:{$teacher->id}:assignment:{$assignment->id}", 30, function () use ($assignment, $teacher) {
                return Assignment::with(['subject', 'submissions.student', 'comments.user'])
                    ->where('id', $assignment->id)
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
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'You do not have permission to view this assignment.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Assignment not found or you do not have permission to access it.']);
        } catch (\Exception $e) {
            \Log::error('Error accessing assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
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
            
            $this->authorize('gradeSubmission', $submission->assignment);

            $this->assignmentService->gradeSubmission(
                $submission,
                (int) $request->marks_obtained,
                $request->teacher_feedback
            );

            return back()->with('success', 'Assignment graded successfully!');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'You do not have permission to grade this assignment.']);
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

    public function delete(Assignment $assignment)
    {
        try {
            $this->authorize('delete', $assignment);

            $teacher = Teacher::where('email', Auth::user()->email)->first();
            
            if (!$teacher) {
                return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
            }

            $subjectId = $assignment->subject_id;
            $assignment->delete();

            return redirect()->route('teacher.subjects.show', $subjectId)
                ->with('success', 'Assignment moved to trash.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'You do not have permission to delete this assignment.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Assignment not found or you do not have permission to delete it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error deleting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to delete assignment. Please try again.']);
        } catch (\Exception $e) {
            \Log::error('Error deleting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignment->id,
                'teacher_id' => $teacher->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred while deleting the assignment. Please try again.']);
        }
    }

    /**
     * Show the trashed assignments.
     */
    public function trashed()
    {
        // Get the teacher record for the authenticated user
        $teacher = \App\Models\Teacher::where('email', auth()->user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->withErrors(['error' => 'Teacher profile not found.']);
        }

        $trashedAssignments = Assignment::onlyTrashed()
            ->whereIn('subject_id', $teacher->subjects->pluck('id'))
            ->with('subject')
            ->get();
            
        return view('teacher.assignments.trashed', compact('trashedAssignments'));
    }

    /**
     * Restore a soft-deleted assignment.
     */
    public function restore($id)
    {
        try {
            $assignment = Assignment::onlyTrashed()->findOrFail($id);
            $this->authorize('restore', $assignment);
            $assignment->restore();

            return redirect()->route('teacher.subjects.show', $assignment->subject_id)->with('success', 'Assignment restored successfully.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return redirect()->route('teacher.assignments.trashed')
                ->withErrors(['error' => 'You do not have permission to restore this assignment.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('teacher.assignments.trashed')
                ->withErrors(['error' => 'Assignment not found.']);
        } catch (\Exception $e) {
            \Log::error('Error restoring assignment: ' . $e->getMessage(), [
                'assignment_id' => $id,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('teacher.assignments.trashed')
                ->withErrors(['error' => 'An error occurred while restoring the assignment. Please try again.']);
        }
    }

    /**
     * Permanently delete an assignment.
     */
    public function forceDelete($id)
    {
        $assignment = Assignment::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $assignment);
        $assignment->forceDelete();

        return redirect()->route('teacher.assignments.trashed')->with('success', 'Assignment permanently deleted.');
    }
}