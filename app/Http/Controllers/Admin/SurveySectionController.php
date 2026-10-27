<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveySectionRequest;
use App\Http\Requests\UpdateSurveySectionRequest;
use App\Models\SurveySection;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;

class SurveySectionController extends Controller
{
    public function store(StoreSurveySectionRequest $request, SurveyDraftManager $surveys): JsonResponse
    {
        $section = $surveys->saveSection($request->validated(), $request->user());

        return response()->json([
            'message' => 'Section added to the draft survey.',
            'focusSectionStableId' => $section->stable_id,
            'state' => $surveys->managementState(),
        ], 201);
    }

    public function update(
        UpdateSurveySectionRequest $request,
        SurveySection $section,
        SurveyDraftManager $surveys,
    ): JsonResponse {
        $section = $surveys->saveSection($request->validated(), $request->user(), $section);

        return response()->json([
            'message' => 'Section changes saved to the draft.',
            'focusSectionStableId' => $section->stable_id,
            'state' => $surveys->managementState(),
        ]);
    }

    public function destroy(SurveySection $section, SurveyDraftManager $surveys): JsonResponse
    {
        $surveys->deleteSection($section, request()->user());

        return response()->json([
            'message' => 'Empty section archived in the draft.',
            'state' => $surveys->managementState(),
        ]);
    }
}
