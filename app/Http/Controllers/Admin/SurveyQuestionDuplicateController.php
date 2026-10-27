<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class SurveyQuestionDuplicateController extends Controller
{
    public function __invoke(SurveyQuestion $question, SurveyDraftManager $surveys): JsonResponse
    {
        $copy = $surveys->duplicateQuestion($question, request()->user());

        return response()->json([
            'message' => 'Question duplicated. Review the independent copy before publishing.',
            'focusStableId' => $copy->stable_id,
            'state' => $surveys->managementState(),
        ], 201);
    }
}
