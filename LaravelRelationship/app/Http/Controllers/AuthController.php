<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Subject;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'teacher'
                ? redirect()->route('teacher.index')
                : redirect()->route('student.index');
        }
        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|in:student,teacher',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'subject_ids' => 'array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $user = User::create([
            'role' => $validated['role'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if ($user->role === 'teacher') {
            Teacher::create([
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        if ($user->role === 'student') {
            $student = Student::create([
                'name' => $user->name,
                'email' => $user->email,
                'classroom_id' => $validated['classroom_id'],
            ]);
            if (!empty($validated['subject_ids'])) {
                $student->subjects()->sync($validated['subject_ids']);
            }
        }

        Auth::login($user);
        return $user->role === 'teacher'
            ? redirect()->route('teacher.index')
            : redirect()->route('student.index');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'teacher'
                ? redirect()->route('teacher.index')
                : redirect()->route('student.index');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|in:student,teacher',
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'role' => $credentials['role']])) {
            $request->session()->regenerate();
            return $credentials['role'] === 'teacher'
                ? redirect()->route('teacher.index')
                : redirect()->route('student.index');
        }

        return back()->withErrors(['email' => 'Invalid credentials or role.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}