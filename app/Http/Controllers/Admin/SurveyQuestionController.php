<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyQuestionRequest;
use App\Http\Requests\UpdateSurveyQuestionRequest;
use App\Models\SurveyQuestion;
use App\SurveyDraftManager;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SurveyQuestionController extends Controller
{
    public function index(SurveyDraftManager $surveys): View
    {
        return view('admin.questions.index', [
            'surveyState' => $surveys->managementState(),
            'questionTypes' => SurveyQuestion::typeLabels(),
            'iconChoices' => $this->iconChoices(),
            'toneChoices' => [
                'blue' => 'Blue',
                'green' => 'Green',
                'orange' => 'Orange',
                'purple' => 'Purple',
                'red' => 'Red',
                'neutral' => 'Gray',
            ],
            'surveyRoutes' => [
                'questions' => url('/admin/questions'),
                'questionOrder' => route('admin.question-order.update'),
                'sections' => url('/admin/sections'),
                'sectionOrder' => route('admin.section-order.update'),
                'publish' => route('admin.published-survey.store'),
                'fullPreview' => route('admin.survey-preview'),
            ],
        ]);
    }

    public function store(StoreSurveyQuestionRequest $request, SurveyDraftManager $surveys): JsonResponse
    {
        $question = $surveys->saveQuestion($request->validated(), $request->user());

        return $this->stateResponse(
            $surveys,
            'Question added to the draft survey.',
            $question->stable_id,
            201,
        );
    }

    public function update(
        UpdateSurveyQuestionRequest $request,
        SurveyQuestion $question,
        SurveyDraftManager $surveys,
    ): JsonResponse {
        $question = $surveys->saveQuestion($request->validated(), $request->user(), $question);

        return $this->stateResponse($surveys, 'Question changes saved to the draft.', $question->stable_id);
    }

    public function destroy(
        SurveyQuestion $question,
        SurveyDraftManager $surveys,
    ): JsonResponse {
        $surveys->deleteQuestion($question, request()->user());

        return $this->stateResponse(
            $surveys,
            'Question removed from the draft. Historical responses were preserved.',
        );
    }

    private function stateResponse(
        SurveyDraftManager $surveys,
        string $message,
        ?string $focusStableId = null,
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'focusStableId' => $focusStableId,
            'state' => $surveys->managementState(),
        ], $status);
    }

    /** @return array<string, string> */
    private function iconChoices(): array
    {
        return [
            'message-circle' => 'Conversation',
            'users' => 'People',
            'calendar' => 'Calendar',
            'calendar-days' => 'Calendar days',
            'clock' => 'Clock',
            'star' => 'Star',
            'thumbs-up' => 'Thumbs up',
            'thumbs-down' => 'Thumbs down',
            'lightbulb' => 'Idea',
            'microphone' => 'Microphone',
            'gamepad' => 'Game',
            'target' => 'Target',
            'help-circle' => 'Help',
            'info' => 'Information',
            'check-circle' => 'Check',
            'globe' => 'Globe',
            'book-open' => 'Book',
            'sparkles' => 'Sparkles',
            'heart' => 'Heart',
            'ban' => 'Not allowed',
            'timer' => 'Timer',
            'sun' => 'Sun',
            'moon' => 'Moon',
        ];
    }
}
