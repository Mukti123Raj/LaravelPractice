<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\AssignmentSubmission;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacherUser = Auth::user();
        $teacher = Teacher::where('email', $teacherUser->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['email' => 'Teacher profile not found.']);
        }
        
        $classrooms = Cache::remember("teacher:{$teacher->id}:dashboard:classrooms", 30, function () use ($teacher) {
            return Classroom::withCount('students')
                ->whereHas('subjects', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })
                ->get();
        });
            
        $subjects = Cache::remember("teacher:{$teacher->id}:dashboard:subjects", 30, function () use ($teacher) {
            return Subject::with('classroom')->where('teacher_id', $teacher->id)->get();
        });
        
        $allClassrooms = Cache::remember('classrooms:all', 30, function () {
            return Classroom::all();
        });
        
        $students = Cache::remember("teacher:{$teacher->id}:dashboard:students", 30, function () use ($classrooms) {
            return Student::with('classroom')
                ->whereIn('classroom_id', $classrooms->pluck('id'))
                ->get();
        });
        
        return view('teacher.dashboard', compact('classrooms', 'subjects', 'allClassrooms', 'students', 'teacher'));
    }

    public function index()
    {
        return $this->dashboard();
    }
    
    public function subjects()
    {
        $teacherUser = Auth::user();
        $teacher = Teacher::where('email', $teacherUser->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['email' => 'Teacher profile not found.']);
        }
        
        $subjects = Cache::remember("teacher:{$teacher->id}:subjects:index", 30, function () use ($teacher) {
            return Subject::with(['classroom', 'assignments', 'students'])
                ->where('teacher_id', $teacher->id)
                ->orderBy('name')
                ->get();
        });
        
        return view('teacher.subjects.index', compact('subjects', 'teacher'));
    }
    
    public function createClassroom(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Classroom::create(['name' => $request->name]);
        
        return redirect()->route('teacher.index');
    }
    
    public function createSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'classroom_id' => 'required|exists:classrooms,id',
        ]);
        
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        if (!$teacher) {
            return back()->withErrors(['teacher' => 'Teacher profile not found.']);
        }
        
        Subject::create([
            'name' => $request->name,
            'teacher_id' => $teacher->id,
            'classroom_id' => $request->classroom_id,
        ]);
        
        return redirect()->route('teacher.index');
    }

    public function deleteClassroom(Classroom $classroom)
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        if (!$teacher) {
            return redirect()->route('teacher.index')->withErrors(['teacher' => 'Teacher profile not found.']);
        }
        
 
        $isOwnedByTeacher = $classroom->subjects()->where('teacher_id', $teacher->id)->exists();
        if (!$isOwnedByTeacher) {
            abort(403, 'You are not authorized to delete this classroom.');
        }
        
        $classroom->delete();
        
        return redirect()->route('teacher.index')->with('status', 'Classroom deleted successfully.');
    }

    public function downloadSubmission($submissionId)
    {
        $teacherUser = Auth::user();
        $teacher = Teacher::where('email', $teacherUser->email)->first();

        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher record not found.']);
        }

        $submission = AssignmentSubmission::with(['assignment.subject'])
            ->where('id', $submissionId)
            ->firstOrFail();

        // Check if the teacher is authorized to access this submission
        if ($submission->assignment->subject->teacher_id !== $teacher->id) {
            abort(403, 'You are not authorized to download this submission.');
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            return back()->withErrors(['error' => 'File not found.']);
        }

        $studentName = $submission->student->name ?? 'Unknown Student';
        $fileName = $studentName . '_' . $submission->assignment->title . '_' . basename($submission->file_path);

        return Storage::disk('public')->download($submission->file_path, $fileName);
    }
}