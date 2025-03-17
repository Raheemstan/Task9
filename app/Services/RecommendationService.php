<?php

namespace App\Services;

use App\Models\User;
use App\Models\Content;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    public function getRecommendations(User $user)
    {
        // Get user's viewed content categories
        $userPreferences = DB::table('content_views')
            ->join('contents', 'content_views.content_id', '=', 'contents.id')
            ->where('content_views.user_id', $user->id)
            ->select('contents.type', DB::raw('count(*) as view_count'))
            ->groupBy('contents.type')
            ->orderByDesc('view_count')
            ->get();

        // Get similar content based on user preferences
        $recommendations = Content::whereIn('type', $userPreferences->pluck('type'))
            ->whereNotIn('id', $user->contents->pluck('id'))
            ->where('access_tier', '<=', $user->subscription_tier)
            ->orderBy('views', 'desc')
            ->take(10)
            ->get();

        return $recommendations;
    }
} 