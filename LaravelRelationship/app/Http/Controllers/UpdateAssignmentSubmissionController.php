<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;

class UpdateAssignmentSubmissionController extends Controller
{
    protected $student;

    public function __construct()
    {
        $user = Auth::user();
        $this->student = $user
            ? Student::where('email', $user->email)->first()
            : null;
    }

    public function __invoke(\App\Http\Requests\Student\StudentUpdateAssignmentRequest $request, $assignmentId)
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
}


