<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Assignment;
use App\Models\Student;
use App\Services\AssignmentService;

class SubmitAssignmentController extends Controller
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

    public function __invoke(\App\Http\Requests\Student\StudentSubmitAssignmentRequest $request, $assignmentId)
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
}


