<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;

class DebugEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:events {--watch : Watch for events in real-time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug and monitor event system in real-time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('watch')) {
            $this->watchEvents();
        } else {
            $this->showEventStatus();
        }
    }
    
    /**
     * Show current event system status
     */
    private function showEventStatus()
    {
        $this->info('🔍 Event System Debug Information');
        $this->line('==================================');
        
        // 1. Event Registration Status
        $this->info('📡 Event Registration:');
        $events = [
            'App\Events\AssignmentCreated',
            'App\Events\AssignmentSubmitted', 
            'App\Events\AssignmentGraded'
        ];
        
        foreach ($events as $event) {
            $listeners = Event::getListeners($event);
            $status = count($listeners) > 0 ? '✅' : '❌';
            $this->line("   {$status} {$event}: " . count($listeners) . " listener(s)");
        }
        
        // 2. Queue Status
        $this->info('⚡ Queue Status:');
        $queueSize = Queue::size();
        $this->line("   📊 Pending jobs: {$queueSize}");
        
        if ($queueSize > 0) {
            $this->line('   📋 Recent jobs:');
            $jobs = DB::table('jobs')->orderBy('created_at', 'desc')->limit(5)->get();
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $command = $payload['data']['commandName'] ?? 'Unknown';
                $this->line("      - {$command} (ID: {$job->id})");
            }
        }
        
        // 3. Failed Jobs
        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $this->warn("   ⚠️  Failed jobs: {$failedJobs}");
            $this->line('   Run: php artisan queue:failed to see details');
        }
        
        // 4. Recent Notifications
        $this->info('📬 Recent Notifications:');
        $notifications = DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        if ($notifications->count() > 0) {
            foreach ($notifications as $notification) {
                $data = json_decode($notification->data, true);
                $type = $data['type'] ?? 'unknown';
                $message = $data['message'] ?? 'No message';
                $this->line("   📝 [{$type}] {$message}");
                $this->line("      Created: {$notification->created_at}");
            }
        } else {
            $this->line('   ℹ️  No recent notifications');
        }
        
        // 5. Event Service Provider Status
        $this->info('🔧 Configuration:');
        $this->line('   📁 EventServiceProvider: ' . (class_exists('App\Providers\EventServiceProvider') ? '✅ Loaded' : '❌ Missing'));
        $this->line('   ⚙️  Queue Driver: ' . config('queue.default'));
        $this->line('   🗄️  Database: ' . config('database.default'));
    }
    
    /**
     * Watch for events in real-time
     */
    private function watchEvents()
    {
        $this->info('👀 Watching for events... (Press Ctrl+C to stop)');
        $this->line('===============================================');
        
        $lastQueueSize = Queue::size();
        $lastNotificationCount = DB::table('notifications')->count();
        
        while (true) {
            $currentQueueSize = Queue::size();
            $currentNotificationCount = DB::table('notifications')->count();
            
            // Check for new queue jobs
            if ($currentQueueSize > $lastQueueSize) {
                $newJobs = $currentQueueSize - $lastQueueSize;
                $this->info("🆕 {$newJobs} new job(s) queued at " . now()->format('H:i:s'));
                
                // Show latest job details
                $latestJob = DB::table('jobs')->orderBy('created_at', 'desc')->first();
                if ($latestJob) {
                    $payload = json_decode($latestJob->payload, true);
                    $command = $payload['data']['commandName'] ?? 'Unknown';
                    $this->line("   📋 Latest: {$command}");
                }
            }
            
            // Check for new notifications
            if ($currentNotificationCount > $lastNotificationCount) {
                $newNotifications = $currentNotificationCount - $lastNotificationCount;
                $this->info("📬 {$newNotifications} new notification(s) at " . now()->format('H:i:s'));
                
                // Show latest notification
                $latestNotification = DB::table('notifications')
                    ->orderBy('created_at', 'desc')
                    ->first();
                if ($latestNotification) {
                    $data = json_decode($latestNotification->data, true);
                    $type = $data['type'] ?? 'unknown';
                    $message = $data['message'] ?? 'No message';
                    $this->line("   📝 [{$type}] {$message}");
                }
            }
            
            $lastQueueSize = $currentQueueSize;
            $lastNotificationCount = $currentNotificationCount;
            
            sleep(2); // Check every 2 seconds
        }
    }
}
