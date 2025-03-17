<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleFailedPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscription;
    protected $attempt;

    public function __construct(Subscription $subscription, int $attempt)
    {
        $this->subscription = $subscription;
        $this->attempt = $attempt;
    }

    public function handle()
    {
        $maxAttempts = config('subscription.payment_retry_attempts', 3);

        if ($this->attempt < $maxAttempts) {
            // Schedule next retry
            self::dispatch($this->subscription, $this->attempt + 1)
                ->delay(now()->addHours(24));
        } else {
            // Enter grace period
            $this->subscription->update([
                'status' => 'GRACE_PERIOD',
                'end_date' => now()->addDays(7)
            ]);

            // Notify user
            $this->subscription->user->notify(new PaymentFailedNotification($this->subscription));
        }
    }
} 