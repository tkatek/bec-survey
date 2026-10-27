<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveSurveyQuestionRequest;
use App\Models\SurveyQuestion;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class SurveyQuestionMoveController extends Controller
{
    public function __invoke(
        MoveSurveyQuestionRequest $request,
        SurveyQuestion $question,
        SurveyDraftManager $surveys,
    ): JsonResponse {
        $surveys->moveQuestion($question, $request->string('direction')->toString(), $request->user());

        return response()->json([
            'message' => 'Question position saved to the draft.',
            'state' => $surveys->managementState(),
        ]);
    }
}
