<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Teacher;

class CheckNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all notifications for students and teachers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking all notifications...');
        
        // Check student notifications
        Student::with('notifications')->chunkById(100, function ($students) {
            foreach ($students as $student) {
                if ($student->notifications->count() > 0) {
                    $this->info("Student: {$student->name} ({$student->email})");
                    foreach ($student->notifications as $notification) {
                        $this->line("  - [{$notification->data['type']}] {$notification->data['message']}");
                        $this->line("    Created: {$notification->created_at}");
                    }
                    $this->line('');
                }
            }
        });
        
        // Check teacher notifications
        Teacher::with('notifications')->chunkById(100, function ($teachers) {
            foreach ($teachers as $teacher) {
                if ($teacher->notifications->count() > 0) {
                    $this->info("Teacher: {$teacher->name} ({$teacher->email})");
                    foreach ($teacher->notifications as $notification) {
                        $this->line("  - [{$notification->data['type']}] {$notification->data['message']}");
                        $this->line("    Created: {$notification->created_at}");
                    }
                    $this->line('');
                }
            }
        });
        
        $this->info('Notification check completed!');
    }
}
