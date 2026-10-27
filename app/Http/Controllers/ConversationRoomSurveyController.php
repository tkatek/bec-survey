<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConversationRoomResponseRequest;
use App\Models\ConversationRoomResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversationRoomSurveyController extends Controller
{
    /**
     * Display the Conversation Room survey.
     */
    public function create(): View
    {
        return view('conversation-room.create');
    }

    /**
     * Store an anonymous Conversation Room survey response.
     */
    public function store(StoreConversationRoomResponseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $response = ConversationRoomResponse::create([
                'survey_version' => 1,
                ...Arr::only($validated, [
                    'attendance_frequency',
                    'attendance_change',
                    'updated_room_use',
                    'ease_of_use',
                    'activity_usefulness',
                    'speaking_comfort',
                    'session_format',
                    'preferred_session_time',
                    'time_zone',
                    'session_length',
                    'attendance_likelihood',
                    'one_change',
                ]),
            ]);

            $multiSelectAnswers = collect([
                'attendance_reasons' => $validated['attendance_reasons'] ?? [],
                'encouragement_changes' => $validated['encouragement_changes'],
                'preferred_days' => $validated['preferred_days'],
            ])->flatMap(fn (array $answers, string $questionKey): array => collect($answers)
                ->map(fn (string $answer): array => [
                    'question_key' => $questionKey,
                    'option_value' => $answer,
                ])
                ->all())
                ->all();

            $response->choices()->createMany($multiSelectAnswers);
        });

        return redirect()->route('conversation-room.thank-you');
    }

    /**
     * Display the survey completion page.
     */
    public function thankYou(): View
    {
        return view('conversation-room.thank-you');
    }
}
