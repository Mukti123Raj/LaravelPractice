<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\AssignmentCreated;
use App\Events\AssignmentGraded;
use App\Events\AssignmentSubmitted;
use App\Listeners\SendAssignmentCreatedNotification;
use App\Listeners\SendAssignmentGradedNotification;
use App\Listeners\SendAssignmentSubmittedNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AssignmentCreated::class => [
            SendAssignmentCreatedNotification::class,
        ],
        AssignmentGraded::class => [
            SendAssignmentGradedNotification::class,
        ],
        AssignmentSubmitted::class => [
            SendAssignmentSubmittedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
