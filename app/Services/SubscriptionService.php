<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use App\Events\SubscriptionUpdated;
use Exception;

class SubscriptionService
{
    public function upgrade(User $user, string $tier, string $paymentMethodId)
    {
        try {
            DB::beginTransaction();

            // Handle Stripe payment
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentMethodId);

            // Create subscription in Stripe
            $stripeSubscription = $user->newSubscription('default', $this->getPlanId($tier))
                ->create($paymentMethodId);

            // Update local subscription
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tier' => $tier,
                    'status' => 'ACTIVE',
                    'start_date' => now(),
                    'end_date' => now()->addMonth(),
                    'auto_renew' => true,
                    'payment_method_id' => $paymentMethodId,
                    'last_payment_date' => now(),
                    'next_billing_date' => now()->addMonth(),
                ]
            );

            DB::commit();
            event(new SubscriptionUpdated($user));

            return $subscription;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancel(User $user)
    {
        try {
            DB::beginTransaction();

            // Cancel Stripe subscription
            $user->subscription()->cancel();

            // Update local subscription
            $subscription = $user->subscription;
            $subscription->update([
                'status' => 'CANCELLED',
                'auto_renew' => false
            ]);

            DB::commit();
            event(new SubscriptionUpdated($user));

            return $subscription;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getPlanId(string $tier): string
    {
        return [
            'BASIC' => config('services.stripe.basic_plan'),
            'PREMIUM' => config('services.stripe.premium_plan'),
        ][$tier];
    }
}
