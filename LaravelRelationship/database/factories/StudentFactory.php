<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             'name'        => $this->faker->name(),       // ✅ student name
            'email'       => $this->faker->unique()->safeEmail(), // optional
            'teacher_id'  => Teacher::factory(),         // ✅ assign teacher
            'classroom_id'=> Classroom::factory(),
        ];
    }
}
