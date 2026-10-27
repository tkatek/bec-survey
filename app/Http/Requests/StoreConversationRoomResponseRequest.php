<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationRoomResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_frequency' => ['required', 'string', Rule::in([
                'Never',
                'Once or twice',
                'About once a week',
                'Several times a week',
                'Almost every day',
            ])],
            'attendance_change' => ['required', 'string', Rule::in([
                'I attend more often now.',
                'My attendance is about the same.',
                'I attend less often now.',
                'I have stopped attending.',
                "I joined after the update / I didn't attend before.",
            ])],
            'attendance_reasons' => ['sometimes', 'array', 'max:12'],
            'attendance_reasons.*' => ['string', 'distinct', Rule::in([
                'My schedule has changed.',
                'The available session times are inconvenient.',
                "I didn't know when sessions were available.",
                "I haven't been motivated to join.",
                "I'm nervous about speaking or making mistakes.",
                "The topics or activities don't interest me.",
                'The sessions are too easy or too difficult.',
                "I don't get enough opportunities to speak.",
                "The group atmosphere or teaching style doesn't suit me.",
                'I have experienced technical problems.',
                'I prefer other ways of practising English.',
                'Other personal reasons.',
            ])],
            'updated_room_use' => ['required', 'string', Rule::in([
                'Yes, several times.',
                'Yes, once.',
                'No, not yet.',
                "I wasn't aware it had been updated.",
            ])],
            'ease_of_use' => ['required', 'string', Rule::in([
                'Very easy',
                'Somewhat easy',
                'Somewhat difficult',
                'Very difficult',
                'Not sure',
            ])],
            'activity_usefulness' => ['required', 'string', Rule::in([
                'Very useful',
                'Somewhat useful',
                'Not very useful',
                'Not useful at all',
                'Not sure yet',
            ])],
            'speaking_comfort' => ['required', 'string', Rule::in([
                'Very comfortable',
                'Somewhat comfortable',
                'Somewhat uncomfortable',
                'Very uncomfortable',
                'It depends on the group or activity.',
            ])],
            'session_format' => ['required', 'string', Rule::in([
                'Small-group conversations',
                'Pair speaking activities',
                'Teacher-led discussion with feedback',
                'Interactive speaking games and challenges',
                'A combination of formats',
            ])],
            'encouragement_changes' => ['required', 'array', 'min:1', 'max:3'],
            'encouragement_changes.*' => ['string', 'distinct', Rule::in([
                'More real-life conversation topics',
                'Smaller groups and more speaking time',
                'Games, role-plays and interactive challenges',
                'More personalised feedback from teachers',
                'Topics organised by English level',
                'Easier navigation and fewer technical problems',
                'More flexible session times',
                'Clear learning goals and progress tracking',
                'More opportunities to choose topics',
                'Friendly sessions for shy learners',
            ])],
            'preferred_session_time' => ['required', 'string', Rule::in([
                'Morning (6 AM–12 PM)',
                'Afternoon (12–5 PM)',
                'Evening (5–9 PM)',
                'Late evening (after 9 PM)',
                'My availability changes.',
            ])],
            'time_zone' => ['required', 'string', Rule::in([
                'Eastern',
                'Central',
                'Mountain',
                'Pacific',
                'Other',
            ])],
            'preferred_days' => ['required', 'array', 'min:1', 'max:7'],
            'preferred_days.*' => ['string', 'distinct', Rule::in([
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ])],
            'session_length' => ['required', 'string', Rule::in([
                '15 minutes',
                '30 minutes',
                '45 minutes',
                '60 minutes',
            ])],
            'attendance_likelihood' => ['required', 'string', Rule::in([
                'Very likely',
                'Somewhat likely',
                'Not sure',
                'Somewhat unlikely',
                'Very unlikely',
            ])],
            'one_change' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages for the survey.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'encouragement_changes.max' => 'Choose up to three changes that would encourage attendance.',
            'one_change.max' => 'Your suggestion may not be longer than 1,000 characters.',
        ];
    }
}
