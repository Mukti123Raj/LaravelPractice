<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Jobs\SendSingleEmail;

class EmailController extends Controller
{
    public function compose()
    {
        $teacher = Teacher::where('email', Auth::user()->email)->first();
        
        if (!$teacher) {
            return redirect()->route('login')->withErrors(['error' => 'Teacher profile not found.']);
        }

        $classrooms = Classroom::whereHas('subjects', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->get();

        return view('teacher.email.compose', compact('classrooms'));
    }

    public function getStudentsByClass(Request $request)
    {
        $classroomId = $request->input('classroom_id');
        
        $classroom = Classroom::with('students')->find($classroomId);
        
        if (!$classroom) {
            return response()->json(['error' => 'Classroom not found'], 404);
        }

        $studentEmails = $classroom->students->pluck('email')->toArray();
        
        return response()->json($studentEmails);
    }

    
}
