<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\TeacherBulkEmail;

class SendSingleEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $subject;
    public $content;
    public $tries = 5;
    public $backoff = [60, 120, 300, 600];

    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $content)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->email)->send(new TeacherBulkEmail($this->subject, $this->content));
            \Log::info('Email sent successfully to: ' . $this->email);
        } catch (\Exception $e) {
            \Log::error('Failed to send email to ' . $this->email . ': ' . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Too many emails per second')) {
                \Log::info('Rate limit hit for ' . $this->email . ', will retry with backoff');
                throw $e;
            }
            
            throw $e;
        }
    }
}
