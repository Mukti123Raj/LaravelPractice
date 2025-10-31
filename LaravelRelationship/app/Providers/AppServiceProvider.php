<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Observers\AssignmentObserver;
use App\Observers\AssignmentSubmissionObserver;
use App\Services\AssignmentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AssignmentService::class, function ($app) {
            return new AssignmentService();
        });

        $this->app->singleton('assignment.service', function ($app) {
            return $app->make(AssignmentService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Assignment::observe(AssignmentObserver::class);
        AssignmentSubmission::observe(AssignmentSubmissionObserver::class);
    }
}
