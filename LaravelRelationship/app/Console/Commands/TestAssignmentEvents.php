<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Classroom;

class TestAssignmentEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:assignment-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the assignment event system by creating sample data and triggering events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Assignment Event System...');
        
        // Create sample data if it doesn't exist
        $teacher = Teacher::firstOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'John Teacher']
        );
        
        $classroom = Classroom::firstOrCreate(
            ['name' => 'Class A'],
            ['description' => 'Test Classroom']
        );
        
        $subject = Subject::firstOrCreate(
            ['name' => 'Mathematics'],
            [
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id
            ]
        );
        
        $student = Student::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Jane Student',
                'classroom_id' => $classroom->id
            ]
        );
        
        // Enroll student in subject
        if (!$subject->students()->where('student_id', $student->id)->exists()) {
            $subject->students()->attach($student->id);
        }
        
        $this->info('Sample data created/verified.');
        
        // Test 1: Create Assignment (should trigger AssignmentCreated event)
        $this->info('Test 1: Creating assignment...');
        $assignment = Assignment::create([
            'title' => 'Test Assignment',
            'description' => 'This is a test assignment',
            'instructions' => 'Complete all questions',
            'total_marks' => 100,
            'due_date' => now()->addDays(7),
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id
        ]);
        
        $this->info("Assignment created with ID: {$assignment->id}");
        $this->info('AssignmentCreated event should have been dispatched.');
        
        // Wait a moment for event processing
        sleep(2);
        
        // Test 2: Submit Assignment (should trigger AssignmentSubmitted event)
        $this->info('Test 2: Submitting assignment...');
        $submission = AssignmentSubmission::create([
            'submission_content' => 'This is my submission',
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now()
        ]);
        
        $this->info("Assignment submitted with ID: {$submission->id}");
        $this->info('AssignmentSubmitted event should have been dispatched.');
        
        // Wait a moment for event processing
        sleep(2);
        
        // Test 3: Grade Assignment (should trigger AssignmentGraded event)
        $this->info('Test 3: Grading assignment...');
        $submission->update([
            'marks_obtained' => 85,
            'teacher_feedback' => 'Good work!',
            'graded_at' => now()
        ]);
        
        $this->info("Assignment graded with marks: {$submission->marks_obtained}");
        $this->info('AssignmentGraded event should have been dispatched.');
        
        // Check notifications
        $this->info('Checking notifications...');
        
        $studentNotifications = $student->notifications()->count();
        $teacherNotifications = $teacher->notifications()->count();
        
        $this->info("Student notifications: {$studentNotifications}");
        $this->info("Teacher notifications: {$teacherNotifications}");
        
        if ($studentNotifications > 0) {
            $this->info('Student received notifications:');
            foreach ($student->notifications as $notification) {
                $this->line("- {$notification->data['message']}");
            }
        }
        
        if ($teacherNotifications > 0) {
            $this->info('Teacher received notifications:');
            foreach ($teacher->notifications as $notification) {
                $this->line("- {$notification->data['message']}");
            }
        }
        
        $this->info('Event system test completed!');
    }
}
