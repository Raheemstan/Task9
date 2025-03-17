<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Services\ContentService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContentController extends Controller
{
    protected $contentService;
    protected $recommendationService;

    public function __construct(
        ContentService $contentService,
        RecommendationService $recommendationService
    ) {
        $this->contentService = $contentService;
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request)
    {
        $contents = $this->contentService->getAccessibleContent(
            auth()->user(),
            $request->query('page', 1)
        );

        return response()->json($contents);
    }

    public function show(Content $content)
    {
        if (!$this->contentService->canAccess(auth()->user(), $content)) {
            return response()->json([
                'message' => 'Subscription required to access this content'
            ], 403);
        }

        return response()->json($content);
    }

    public function recommendations()
    {
        $cacheKey = 'recommendations_' . auth()->id();
        
        $recommendations = Cache::remember($cacheKey, 3600, function () {
            return $this->recommendationService->getRecommendations(auth()->user());
        });

        return response()->json($recommendations);
    }
} 