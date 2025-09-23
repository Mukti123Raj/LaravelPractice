<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Assignment;
use App\Notifications\PendingAssignmentsSummaryNotification;

class SendPendingAssignmentsSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:notify-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary notification of pending/overdue assignments to each student';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting pending assignments summary notifications...');

        // Eager-load relations we will use to minimize queries
        $students = Student::with(['subjects.assignments' => function ($q) {
            $q->whereDate('due_date', '>=', now()->subYears(1));
        }, 'assignmentSubmissions'])->get();

        $totalNotified = 0;

        foreach ($students as $student) {
            // Build a map of assignment_id -> submission for quick lookup
            $submissionByAssignmentId = $student->assignmentSubmissions->keyBy('assignment_id');

            $pendingAssignments = [];
            $overdueAssignments = [];

            foreach ($student->subjects as $subject) {
                foreach ($subject->assignments as $assignment) {
                    $submission = $submissionByAssignmentId->get($assignment->id);

                    if (!$submission) {
                        // Not submitted
                        if ($assignment->due_date->isPast()) {
                            $overdueAssignments[] = $assignment;
                        } else {
                            $pendingAssignments[] = $assignment;
                        }
                    }
                }
            }

            $pendingCount = count($pendingAssignments);
            $overdueCount = count($overdueAssignments);

            if ($pendingCount > 0 || $overdueCount > 0) {
                $student->notify(new PendingAssignmentsSummaryNotification($pendingCount, $overdueCount));
                $totalNotified++;
                $this->line(" - Notified {$student->name} ({$student->email}) | pending={$pendingCount} overdue={$overdueCount}");
            }
        }

        $this->info("Completed. Total students notified: {$totalNotified}");
        return self::SUCCESS;
    }
}


