<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        // Show only the logged-in student's details
        $student = Auth::user();
        return view('index', ['students' => [$student]]);
    }
}
