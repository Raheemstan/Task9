<?php

namespace App\Services;

use App\Models\User;
use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class ContentService
{
    public function getAccessibleContent(User $user, int $page = 1)
    {
        $cacheKey = "user_{$user->id}_content_page_{$page}";
        
        return Cache::remember($cacheKey, 3600, function () use ($user) {
            return Content::where('access_tier', '<=', $user->subscription_tier)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        });
    }

    public function canAccess(User $user, Content $content): bool
    {
        // Check subscription tier access
        if ($content->access_tier > $user->subscription_tier) {
            return false;
        }

        // Check monthly view limit for free users
        if ($user->subscription_tier === 'FREE') {
            $monthlyLimit = config('subscription.free_tier_monthly_limit', 5);
            if ($user->monthly_view_count >= $monthlyLimit) {
                return false;
            }
        }

        return true;
    }

    public function incrementViewCount(User $user, Content $content): void
    {
        // Increment content views
        $content->increment('views');

        // Increment user's monthly view count if they're on free tier
        if ($user->subscription_tier === 'FREE') {
            $user->increment('monthly_view_count');
        }

        // Record the view in the pivot table
        $user->contents()->attach($content->id);
    }
} 