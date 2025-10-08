<?php

namespace App\Interfaces;

interface StudentRepositoryInterface
{
    public function getAllStudents();

    public function getStudentById($studentId);


    public function deleteStudent($studentId);

    public function createStudent(array $studentDetails);

    public function updateStudent($studentId, array $newDetails);

    public function getStudentByEmail($email);

    public function getStudentsWithRelationships(array $relationships = []);
}
