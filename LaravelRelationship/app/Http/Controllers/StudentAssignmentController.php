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
use App\Services\AssignmentService;

class StudentAssignmentController extends Controller
{
    protected $student;
    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $user = Auth::user();
        $this->student = $user
            ? Student::where('email', $user->email)->first()
            : null;
        $this->assignmentService = $assignmentService;
    }

    public function index()
    {
        try {
            if (!$this->student) {
                return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
            }

            $student = $this->student;
            $assignments = $this->assignmentService->getStudentAssignments(Auth::user());
            return view('student.assignments', compact('assignments', 'student'));
        } catch (\Exception $e) {
            \Log::error('Error loading student assignments: ' . $e->getMessage(), [
                'student_id' => $this->student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('student.dashboard')
                ->withErrors(['error' => 'An error occurred while loading assignments. Please try again.']);
        }
    }

    public function show($assignmentId)
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.assignments')
                ->withErrors(['error' => 'Assignment not found or you do not have permission to access it.']);
        } catch (\Exception $e) {
            \Log::error('Error accessing student assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $this->student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('student.assignments')
                ->withErrors(['error' => 'An error occurred while loading the assignment. Please try again.']);
        }
    }

    public function submit(\App\Http\Requests\Student\StudentSubmitAssignmentRequest $request, $assignmentId)
    {
        try {
            if (!$this->student) {
                return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
            }

            $student = $this->student;
            $assignment = Assignment::where('id', $assignmentId)
                ->whereHas('subject.students', function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                })
                ->firstOrFail();

            $file = $request->hasFile('submission_file') ? $request->file('submission_file') : null;
            $this->assignmentService->submitAssignment(
                $assignment,
                $student,
                $request->submission_content,
                $file
            );

            return redirect()->route('student.assignments')
                ->with('success', 'Assignment submitted successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.assignments')
                ->withErrors(['error' => 'Assignment not found or you do not have permission to submit to it.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error submitting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $this->student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to submit assignment. Please try again.'])
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error submitting assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $this->student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred while submitting the assignment. Please try again.'])
                ->withInput();
        }
    }

    public function updateSubmission(\App\Http\Requests\Student\StudentUpdateAssignmentRequest $request, $assignmentId)
    {
        try {
            if (!$this->student) {
                return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
            }

            $student = $this->student;
            $assignment = Assignment::where('id', $assignmentId)
                ->whereHas('subject.students', function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                })
                ->firstOrFail();

            if (now()->gt($assignment->due_date)) {
                return back()->withErrors(['submission' => 'Assignment deadline has passed.']);
            }

            $submission = AssignmentSubmission::where('assignment_id', $assignmentId)
                ->where('student_id', $student->id)
                ->firstOrFail();

            if ($submission->graded_at) {
                return back()->withErrors(['submission' => 'Cannot update submission after grading.']);
            }

            $filePath = $submission->file_path;
            if ($request->hasFile('submission_file')) {
                try {
                    if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                        Storage::disk('public')->delete($submission->file_path);
                    }
                    
                    $file = $request->file('submission_file');
                    $fileName = time() . '_' . $student->id . '_' . $assignmentId . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('assignment_submissions', $fileName, 'public');
                } catch (\Exception $fileError) {
                    \Log::error('File update error: ' . $fileError->getMessage(), [
                        'assignment_id' => $assignmentId,
                        'student_id' => $student->id,
                        'file_name' => $request->file('submission_file')->getClientOriginalName()
                    ]);
                    return back()->withErrors(['submission_file' => 'Failed to update file. Please try again.'])
                        ->withInput();
                }
            }

            $submission->update([
                'submission_content' => $request->submission_content,
                'file_path' => $filePath,
                'submitted_at' => now()
            ]);

            return redirect()->route('student.assignments')
                ->with('success', 'Assignment updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.assignments')
                ->withErrors(['error' => 'Assignment or submission not found.']);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error updating assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $this->student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'Failed to update assignment. Please try again.'])
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating assignment: ' . $e->getMessage(), [
                'assignment_id' => $assignmentId,
                'student_id' => $this->student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An unexpected error occurred while updating the assignment. Please try again.'])
                ->withInput();
        }
    }

    public function download($submissionId)
    {
        try {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('student.assignments')
                ->withErrors(['error' => 'Submission not found or you do not have permission to download it.']);
        } catch (\Illuminate\Http\Exceptions\FileNotFoundException $e) {
            \Log::error('File not found for download: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'student_id' => $student->id ?? null,
                'file_path' => $submission->file_path ?? null
            ]);
            return back()->withErrors(['error' => 'File not found on server.']);
        } catch (\Exception $e) {
            \Log::error('Error downloading file: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'student_id' => $student->id ?? null,
                'user_id' => Auth::id()
            ]);
            return back()->withErrors(['error' => 'An error occurred while downloading the file. Please try again.']);
        }
    }
}