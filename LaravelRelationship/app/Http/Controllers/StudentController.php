<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Student;
use App\Models\Subject;
use App\Interfaces\StudentRepositoryInterface;

class StudentController extends Controller
{
    protected $studentRepository;

    public function __construct(StudentRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }
    public function dashboard()
    {
        $user = Auth::user();
        $student = Cache::tags(["student:{$user->id}", "assignments"])->remember("student:{$user->id}:dashboard", 30, function () use ($user) {
            return $this->studentRepository->getStudentByEmail($user->email);
        });

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $student->load(['classroom', 'subjects.teacher', 'subjects.assignments.submissions' => function ($query) use ($user) {
            $query->where('student_id', $user->id);
        }]);

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
        $student = Cache::remember("student:{$user->id}:subjects", 30, function () use ($user) {
            $student = $this->studentRepository->getStudentByEmail($user->email);
            if ($student) {
                $student->load(['classroom', 'subjects.teacher']);
            }
            return $student;
        });

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $availableSubjects = Cache::remember("student:{$student->id}:available-subjects", 30, function () use ($student) {
            return Subject::with('teacher')
                ->where('classroom_id', $student->classroom_id)
                ->whereDoesntHave('students', function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                })
                ->get();
        });

        return view('student.subjects', compact('student', 'availableSubjects'));
    }

    public function addSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id'
        ]);

        $user = Auth::user();
        $student = $this->studentRepository->getStudentByEmail($user->email);

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
        $student = $this->studentRepository->getStudentByEmail($user->email);

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }

        $subject = Subject::findOrFail($request->subject_id);

        // Remove the student from the subject
        $student->subjects()->detach($request->subject_id);

        return redirect()->route('student.subjects')->with('success', 'Successfully unenrolled from ' . $subject->name . '!');
    }

    public function indexAll()
    {
        $students = $this->studentRepository->getAllStudents();
        return view('admin.students.index', compact('students'));
    }

    public function show($id)
    {
        $student = $this->studentRepository->getStudentById($id);
        
        if (!$student) {
            return redirect()->back()->withErrors(['error' => 'Student not found.']);
        }

        return view('admin.students.show', compact('student'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'classroom_id' => 'required|exists:classrooms,id'
        ]);

        $studentDetails = $request->only(['name', 'email', 'classroom_id']);
        $student = $this->studentRepository->createStudent($studentDetails);

        return redirect()->route('admin.students.show', $student->id)
            ->with('success', 'Student created successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $id,
            'classroom_id' => 'required|exists:classrooms,id'
        ]);

        $newDetails = $request->only(['name', 'email', 'classroom_id']);
        $student = $this->studentRepository->updateStudent($id, $newDetails);

        if (!$student) {
            return redirect()->back()->withErrors(['error' => 'Student not found.']);
        }

        return redirect()->route('admin.students.show', $student->id)
            ->with('success', 'Student updated successfully!');
    }

    public function destroy($id)
    {
        $deleted = $this->studentRepository->deleteStudent($id);

        if (!$deleted) {
            return redirect()->back()->withErrors(['error' => 'Student not found.']);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully!');
    }
}