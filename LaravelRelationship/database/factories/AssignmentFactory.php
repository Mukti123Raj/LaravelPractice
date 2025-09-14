<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'instructions' => $this->faker->paragraph(),
            'total_marks' => $this->faker->numberBetween(10, 100),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'subject_id' => \App\Models\Subject::factory(),
            'teacher_id' => \App\Models\Teacher::factory(),
        ];
    }
}
