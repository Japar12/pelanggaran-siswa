<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\ViolationCreated::class => [
            \App\Listeners\SendViolationNotification::class,
        ],
        \App\Events\ViolationStatusUpdated::class => [
        \App\Listeners\SendViolationStatusNotification::class,
        ],
    ];

    public function boot(): void {}
}
