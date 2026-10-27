<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderSurveySectionsRequest;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class SurveySectionOrderController extends Controller
{
    public function __invoke(ReorderSurveySectionsRequest $request, SurveyDraftManager $surveys): JsonResponse
    {
        $surveys->reorderSections($request->validated('section_stable_ids'), $request->user());

        return response()->json([
            'message' => 'Section order saved to the draft.',
            'state' => $surveys->managementState(),
        ]);
    }
}
