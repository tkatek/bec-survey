<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoveSurveySectionRequest;
use App\Models\SurveySection;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class SurveySectionMoveController extends Controller
{
    public function __invoke(
        MoveSurveySectionRequest $request,
        SurveySection $section,
        SurveyDraftManager $surveys,
    ): JsonResponse {
        $surveys->moveSection($section, $request->string('direction')->toString(), $request->user());

        return response()->json([
            'message' => 'Section position saved to the draft.',
            'state' => $surveys->managementState(),
        ]);
    }
}
