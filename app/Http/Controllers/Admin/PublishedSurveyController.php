<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class PublishedSurveyController extends Controller
{
    public function __invoke(SurveyDraftManager $surveys): JsonResponse
    {
        $version = $surveys->publish(request()->user());

        return response()->json([
            'message' => "Survey version {$version->number} is now live.",
            'state' => $surveys->managementState(),
        ]);
    }
}
