<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';
    protected $description = 'Process subscription renewals';

    public function handle(SubscriptionService $subscriptionService)
    {
        $subscriptions = Subscription::where('auto_renew', true)
            ->where('next_billing_date', '<=', now()->addDays(7))
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->info("Processing renewal for subscription {$subscription->id}");
            
            try {
                $subscriptionService->processRenewal($subscription);
                $this->info("Successfully renewed subscription {$subscription->id}");
            } catch (\Exception $e) {
                $this->error("Failed to renew subscription {$subscription->id}: {$e->getMessage()}");
            }
        }
    }
} 