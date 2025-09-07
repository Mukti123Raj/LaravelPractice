<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Auth;

Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->intended('/');
    }
    return view('login');
})->name('login');

Route::get('/register', function () {
    if (Auth::check()) {
        return redirect('/');
    }
    return view('register');
})->name('register');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    // Registration logic here
    // Validate input
    $validated = $request->validate([
        'role' => 'required|in:student,teacher',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'classroom' => 'nullable|string',
        'subject' => 'nullable|string',
    ]);

    $user = new \App\Models\User();
    $user->role = $validated['role'];
    $user->name = $validated['name'];
    $user->email = $validated['email'];
    $user->password = bcrypt($validated['password']);
    if ($validated['role'] === 'student') {
        $user->classroom = $validated['classroom'] ?? null;
    } elseif ($validated['role'] === 'teacher') {
        $user->subject = $validated['subject'] ?? null;
    }
    $user->save();

    Auth::login($user);
    if ($user->role === 'teacher') {
        return redirect()->route('teacher.index');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.index');
    }
    return redirect('/');
});

use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student', [StudentController::class, 'index'])->name('student.index');
});
