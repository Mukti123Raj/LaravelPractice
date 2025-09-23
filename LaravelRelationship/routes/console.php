<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: daily at 00:00
Schedule::command('assignments:notify-pending')
    ->timezone('Asia/Kolkata')
    ->dailyAt('00:00')
    ->withoutOverlapping();
