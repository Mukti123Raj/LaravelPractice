<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;

class TeacherController extends Controller
{
    public function index()
    {
        $teacherUser = Auth::user();
        $teacher = Teacher::where('email', $teacherUser->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['email' => 'Teacher profile not found.']);
        }
        
        $classrooms = Classroom::withCount('students')
            ->whereHas('subjects', function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            })
            ->get();
            
        $subjects = Subject::with('classroom')->where('teacher_id', $teacher->id)->get();
        
        $allClassrooms = Classroom::all();
        
        return view('teacher', compact('classrooms', 'subjects', 'allClassrooms'));
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
}