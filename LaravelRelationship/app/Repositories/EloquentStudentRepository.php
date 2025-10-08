<?php

namespace App\Repositories;

use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class EloquentStudentRepository implements StudentRepositoryInterface
{

    public function getAllStudents(): Collection
    {
        return Student::all();
    }

    public function getStudentById($studentId): ?Student
    {
        return Student::find($studentId);
    }

    public function deleteStudent($studentId): bool
    {
        $student = Student::find($studentId);
        if ($student) {
            return $student->delete();
        }
        return false;
    }

    public function createStudent(array $studentDetails): Student
    {
        return Student::create($studentDetails);
    }

    public function updateStudent($studentId, array $newDetails): ?Student
    {
        $student = Student::find($studentId);
        if ($student) {
            $student->update($newDetails);
            return $student->fresh();
        }
        return null;
    }

    public function getStudentByEmail($email): ?Student
    {
        return Student::where('email', $email)->first();
    }

    public function getStudentsWithRelationships(array $relationships = []): Collection
    {
        $query = Student::query();
        
        if (!empty($relationships)) {
            $query->with($relationships);
        }
        
        return $query->get();
    }
}

