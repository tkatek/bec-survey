<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderSurveyQuestionsRequest;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class SurveyQuestionOrderController extends Controller
{
    public function __invoke(ReorderSurveyQuestionsRequest $request, SurveyDraftManager $surveys): JsonResponse
    {
        $surveys->reorderQuestions($request->validated('groups'), $request->user());

        return response()->json([
            'message' => 'Question order saved to the draft.',
            'state' => $surveys->managementState(),
        ]);
    }
}
