<?php

namespace App\Http\Requests;

use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSurveyQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $editingVersion = SurveyVersion::query()->draft()->first() ?? SurveyVersion::query()->active()->first();

        return [
            'section_stable_id' => [
                'required',
                'uuid',
                Rule::exists('survey_sections', 'stable_id')->where(
                    fn ($query) => $query
                        ->when($editingVersion, fn ($versionQuery) => $versionQuery->where('survey_version_id', $editingVersion->id))
                        ->whereNull('deleted_at'),
                ),
            ],
            'position' => ['required', 'integer', 'min:1', 'max:65535'],
            'prompt' => ['required', 'string', 'max:500'],
            'help_text' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(array_keys(SurveyQuestion::typeLabels()))],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'max_selections' => [
                Rule::requiredIf($this->input('type') === SurveyQuestion::TYPE_MULTIPLE_CHOICE),
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],
            'options' => [
                Rule::excludeIf($this->input('type') === SurveyQuestion::TYPE_LONG_TEXT),
                Rule::requiredIf($this->input('type') !== SurveyQuestion::TYPE_LONG_TEXT),
                'array',
                'min:2',
                'max:20',
            ],
            'options.*.stable_id' => ['nullable', 'uuid', 'distinct:strict'],
            'options.*.label' => ['required', 'string', 'max:255', 'distinct:ignore_case'],
            'options.*.icon' => ['required', 'string', Rule::in($this->allowedIcons())],
            'options.*.tone' => ['required', 'string', Rule::in(['blue', 'green', 'orange', 'purple', 'red', 'neutral'])],
            'options.*.group_name' => [
                Rule::requiredIf($this->input('type') === SurveyQuestion::TYPE_GROUPED_CHOICE),
                'nullable',
                'string',
                'max:60',
            ],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['type', 'max_selections', 'options'])) {
                    return;
                }

                $sourceQuestion = $this->route('question');
                $allowedStableIds = collect();

                if ($sourceQuestion instanceof SurveyQuestion) {
                    $editingVersion = SurveyVersion::query()->draft()->first() ?? SurveyVersion::query()->active()->first();
                    $allowedStableIds = $editingVersion?->questions()
                        ->where('stable_id', $sourceQuestion->stable_id)
                        ->first()
                        ?->options()
                        ->pluck('stable_id') ?? collect();
                }

                foreach ($this->array('options') as $index => $option) {
                    $stableId = $option['stable_id'] ?? null;

                    if (filled($stableId) && ! $allowedStableIds->contains($stableId)) {
                        $validator->errors()->add(
                            "options.{$index}.stable_id",
                            'This answer option identifier does not belong to the question being edited.',
                        );
                    }
                }

                if ($this->input('type') !== SurveyQuestion::TYPE_MULTIPLE_CHOICE) {
                    return;
                }

                $optionCount = count($this->array('options'));

                if ($this->integer('max_selections') > $optionCount) {
                    $validator->errors()->add(
                        'max_selections',
                        'The maximum selections cannot exceed the number of answer options.',
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'options.required' => 'Add at least two answer options for this question.',
            'options.*.label.required' => 'Every answer option needs a label.',
            'options.*.label.distinct' => 'Answer option labels must be different.',
            'options.*.stable_id.distinct' => 'Answer option identifiers must be different.',
            'options.*.group_name.required' => 'Every grouped option needs a group name.',
            'max_selections.required' => 'Set the maximum number of choices students may select.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allowedIcons(): array
    {
        return [
            'alert-circle', 'ban', 'bar-chart', 'book-open', 'calendar', 'calendar-days',
            'calendar-star', 'check', 'check-circle', 'clock', 'compass', 'ellipsis',
            'file-text', 'gamepad', 'gauge', 'globe', 'hand-pointer', 'help-circle',
            'heart', 'home', 'info', 'layers', 'layout-grid', 'lightbulb', 'message-circle',
            'microphone', 'microphone-off', 'minus', 'moon', 'refresh', 'shield',
            'smile', 'sparkles', 'star', 'stop-circle', 'sun', 'sunset', 'target',
            'thumbs-down', 'thumbs-up', 'timer', 'trending-down', 'trending-up',
            'user-plus', 'user-round', 'users', 'wifi',
        ];
    }
}
