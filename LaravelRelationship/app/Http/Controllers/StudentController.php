<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = Student::with(['classroom', 'subjects.teacher'])
            ->where('email', $user->email)
            ->first();

        if (!$student) {
            return redirect()->route('login')->withErrors(['error' => 'Student record not found.']);
        }
        
        return view('student', compact('student'));
    }
}