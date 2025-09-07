<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class TeacherController extends Controller
{
    public function index()
    {
        // Show all students for the teacher
        $students = Student::all();
        return view('index', ['students' => $students]);
    }
}
