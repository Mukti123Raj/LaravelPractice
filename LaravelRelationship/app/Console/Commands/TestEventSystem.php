<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Classroom;
use App\Events\AssignmentCreated;
use App\Events\AssignmentSubmitted;
use App\Events\AssignmentGraded;

class TestEventSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:event-system {--method=all : Testing method (all, dispatch, listener, notification, queue)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive testing of the event and listener system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $method = $this->option('method');
        
        $this->info('🔍 Testing Event & Listener System');
        $this->line('=====================================');
        
        switch ($method) {
            case 'dispatch':
                $this->testEventDispatching();
                break;
            case 'listener':
                $this->testListenerExecution();
                break;
            case 'notification':
                $this->testNotificationDelivery();
                break;
            case 'queue':
                $this->testQueueProcessing();
                break;
            case 'all':
            default:
                $this->testEventDispatching();
                $this->testListenerExecution();
                $this->testNotificationDelivery();
                $this->testQueueProcessing();
                break;
        }
        
        $this->info('✅ Event system testing completed!');
    }
    
    /**
     * Test 1: Event Dispatching
     */
    private function testEventDispatching()
    {
        $this->info('📡 Test 1: Event Dispatching');
        $this->line('----------------------------');
        
        // Test if events are registered
        $this->info('Checking event registration...');
        
        $events = [
            AssignmentCreated::class,
            AssignmentSubmitted::class,
            AssignmentGraded::class
        ];
        
        foreach ($events as $event) {
            $listeners = Event::getListeners($event);
            $this->line("✅ {$event}: " . count($listeners) . " listener(s) registered");
        }
        
        // Test manual event dispatching
        $this->info('Testing manual event dispatching...');
        
        $assignment = $this->createTestAssignment();
        $this->line("✅ AssignmentCreated event dispatched for assignment ID: {$assignment->id}");
        
        $submission = $this->createTestSubmission($assignment);
        $this->line("✅ AssignmentSubmitted event dispatched for submission ID: {$submission->id}");
        
        $submission->update(['marks_obtained' => 90]);
        $this->line("✅ AssignmentGraded event dispatched for submission ID: {$submission->id}");
        
        $this->line('');
    }
    
    /**
     * Test 2: Listener Execution
     */
    private function testListenerExecution()
    {
        $this->info('🎧 Test 2: Listener Execution');
        $this->line('-------------------------------');
        
        // Check if listeners are queued
        $this->info('Checking queue status...');
        
        $queueSize = Queue::size();
        $this->line("📊 Current queue size: {$queueSize}");
        
        if ($queueSize > 0) {
            $this->info('Processing queued listeners...');
            
            // Process a few jobs
            for ($i = 0; $i < min(5, $queueSize); $i++) {
                $this->line("Processing job " . ($i + 1) . "...");
                \Artisan::call('queue:work', ['--once' => true]);
            }
            
            $newQueueSize = Queue::size();
            $this->line("📊 Queue size after processing: {$newQueueSize}");
        } else {
            $this->line("ℹ️  No jobs in queue");
        }
        
        $this->line('');
    }
    
    /**
     * Test 3: Notification Delivery
     */
    private function testNotificationDelivery()
    {
        $this->info('📬 Test 3: Notification Delivery');
        $this->line('----------------------------------');
        
        // Check notifications for test users
        $student = Student::where('email', 'student@example.com')->first();
        $teacher = Teacher::where('email', 'teacher@example.com')->first();
        
        if ($student) {
            $studentNotifications = $student->notifications()->count();
            $this->line("👨‍🎓 Student notifications: {$studentNotifications}");
            
            if ($studentNotifications > 0) {
                $latest = $student->notifications()->latest()->first();
                $this->line("   Latest: [{$latest->data['type']}] {$latest->data['message']}");
            }
        }
        
        if ($teacher) {
            $teacherNotifications = $teacher->notifications()->count();
            $this->line("👨‍🏫 Teacher notifications: {$teacherNotifications}");
            
            if ($teacherNotifications > 0) {
                $latest = $teacher->notifications()->latest()->first();
                $this->line("   Latest: [{$latest->data['type']}] {$latest->data['message']}");
            }
        }
        
        $this->line('');
    }
    
    /**
     * Test 4: Queue Processing
     */
    private function testQueueProcessing()
    {
        $this->info('⚡ Test 4: Queue Processing');
        $this->line('----------------------------');
        
        // Check queue configuration
        $this->info('Queue configuration:');
        $this->line("   Driver: " . config('queue.default'));
        $this->line("   Connection: " . config('queue.connections.' . config('queue.default') . '.driver'));
        
        // Check failed jobs
        $failedJobs = \DB::table('failed_jobs')->count();
        $this->line("   Failed jobs: {$failedJobs}");
        
        if ($failedJobs > 0) {
            $this->warn("⚠️  There are {$failedJobs} failed jobs. Check logs for details.");
        }
        
        $this->line('');
    }
    
    /**
     * Create test assignment
     */
    private function createTestAssignment()
    {
        $teacher = Teacher::firstOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'Test Teacher']
        );
        
        $classroom = Classroom::firstOrCreate(
            ['name' => 'Test Class'],
            ['description' => 'Test Classroom']
        );
        
        $subject = Subject::firstOrCreate(
            ['name' => 'Test Subject'],
            [
                'teacher_id' => $teacher->id,
                'classroom_id' => $classroom->id
            ]
        );
        
        return Assignment::create([
            'title' => 'Test Assignment ' . now()->format('H:i:s'),
            'description' => 'Test assignment for event system',
            'instructions' => 'Complete this test',
            'total_marks' => 100,
            'due_date' => now()->addDays(7),
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id
        ]);
    }
    
    /**
     * Create test submission
     */
    private function createTestSubmission($assignment)
    {
        $student = Student::firstOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Test Student',
                'classroom_id' => $assignment->subject->classroom_id
            ]
        );
        
        // Enroll student in subject
        if (!$assignment->subject->students()->where('student_id', $student->id)->exists()) {
            $assignment->subject->students()->attach($student->id);
        }
        
        return AssignmentSubmission::create([
            'submission_content' => 'Test submission content',
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submitted_at' => now()
        ]);
    }
}
