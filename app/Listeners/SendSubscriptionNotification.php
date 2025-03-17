<?php

namespace App\Listeners;

use App\Events\SubscriptionUpdated;
use App\Notifications\SubscriptionStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSubscriptionNotification implements ShouldQueue
{
    public function handle(SubscriptionUpdated $event): void
    {
        $event->user->notify(new SubscriptionStatusNotification($event->user->subscription));
    }
} 