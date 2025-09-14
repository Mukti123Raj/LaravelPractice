<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Subject;

class StudentController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $student = Student::with(['classroom', 'subjects.teacher', 'subjects.assignments.submissions' => function ($query) use ($user) {
            $query->where('student_id', $user->id);
        }])
            ->where('email', $user->email)
            ->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        // Get assignment statistics
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

        $assignmentStats = [
            'total' => $assignments->count(),
            'done_with_marks' => $assignments->where('status', 'done_with_marks')->count(),
            'submitted' => $assignments->where('status', 'submitted')->count(),
            'late_submit' => $assignments->where('status', 'late_submit')->count(),
            'due_soon' => $assignments->where('status', 'due_soon')->count(),
            'overdue' => $assignments->where('status', 'overdue')->count(),
            'not_submitted' => $assignments->where('status', 'not_submitted')->count()
        ];
        
        return view('student.dashboard', compact('student', 'assignmentStats'));
    }

    public function index()
    {
        return $this->dashboard();
    }

    public function subjects()
    {
        $user = Auth::user();
        $student = Student::with(['classroom', 'subjects.teacher'])
            ->where('email', $user->email)
            ->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $availableSubjects = Subject::with('teacher')
            ->where('classroom_id', $student->classroom_id)
            ->whereDoesntHave('students', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->get();

        return view('student.subjects', compact('student', 'availableSubjects'));
    }

    public function addSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $subject = Subject::findOrFail($request->subject_id);

        // Check if the subject belongs to the student's classroom
        if ($subject->classroom_id !== $student->classroom_id) {
            return back()->withErrors(['subject' => 'You can only enroll in subjects from your classroom.']);
        }

        // Check if student is already enrolled in this subject
        if ($student->subjects()->where('subject_id', $request->subject_id)->exists()) {
            return back()->withErrors(['subject' => 'You are already enrolled in this subject.']);
        }

        // Enroll the student in the subject
        $student->subjects()->attach($request->subject_id);

        return redirect()->route('student.subjects')->with('success', 'Successfully enrolled in ' . $subject->name . '!');
    }

    public function removeSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $subject = Subject::findOrFail($request->subject_id);

        // Remove the student from the subject
        $student->subjects()->detach($request->subject_id);

        return redirect()->route('student.subjects')->with('success', 'Successfully unenrolled from ' . $subject->name . '!');
    }
}