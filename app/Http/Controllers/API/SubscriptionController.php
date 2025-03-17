<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Cache;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function current()
    {
        $subscription = Cache::remember('subscription_' . auth()->id(), 3600, function () {
            return auth()->user()->subscription;
        });

        return response()->json($subscription);
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'tier' => 'required|in:BASIC,PREMIUM',
            'payment_method_id' => 'required|string'
        ]);

        $result = $this->subscriptionService->upgrade(
            auth()->user(),
            $request->tier,
            $request->payment_method_id
        );

        Cache::forget('subscription_' . auth()->id());

        return response()->json($result);
    }

    public function cancel()
    {
        $result = $this->subscriptionService->cancel(auth()->user());
        Cache::forget('subscription_' . auth()->id());
        
        return response()->json($result);
    }
} 