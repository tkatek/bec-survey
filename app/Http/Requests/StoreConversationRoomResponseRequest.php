<?php

namespace App\Http\Requests;

use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreConversationRoomResponseRequest extends FormRequest
{
    /** @var Collection<int, SurveyQuestion>|null */
    private ?Collection $surveyQuestions = null;

    private ?SurveyVersion $surveyVersion = null;

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
        $rules = [
            'survey_version' => [
                'required',
                'integer',
                Rule::exists('survey_versions', 'number')->where(
                    fn ($query) => $query->where('status', '!=', SurveyVersion::STATUS_DRAFT),
                ),
            ],
            'answers' => ['required', 'array'],
        ];

        foreach ($this->questions() as $question) {
            $answerKey = 'answers.'.$question->id;

            if ($question->type === SurveyQuestion::TYPE_LONG_TEXT) {
                $rules[$answerKey] = [
                    $question->is_required ? 'required' : 'nullable',
                    'string',
                    'max:1000',
                ];

                continue;
            }

            $optionExists = Rule::exists('survey_question_options', 'id')
                ->where(fn ($query) => $query
                    ->where('survey_question_id', $question->id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'));

            if ($question->type === SurveyQuestion::TYPE_SINGLE_CHOICE) {
                $rules[$answerKey] = [
                    $question->is_required ? 'required' : 'nullable',
                    'integer',
                    $optionExists,
                ];

                continue;
            }

            $answerRules = [$question->is_required ? 'required' : 'sometimes', 'array'];

            if ($question->is_required) {
                $answerRules[] = 'min:1';
            }

            if ($question->type === SurveyQuestion::TYPE_GROUPED_CHOICE) {
                $answerRules[] = 'size:'.$question->options->pluck('group_name')->filter()->unique()->count();
            } elseif ($question->max_selections) {
                $answerRules[] = 'max:'.$question->max_selections;
            }

            $rules[$answerKey] = $answerRules;
            $rules[$answerKey.'.*'] = ['integer', 'distinct', $optionExists];
        }

        return $rules;
    }

    /**
     * Get custom validation messages for the survey.
     *
     * @return array<string, string>
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->questions()->where('type', SurveyQuestion::TYPE_GROUPED_CHOICE) as $question) {
                $selectedIds = collect($this->input('answers.'.$question->id, []))->map(fn ($id): int => (int) $id);
                $selectedGroups = $question->options
                    ->whereIn('id', $selectedIds)
                    ->pluck('group_name')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
                $requiredGroups = $question->options
                    ->pluck('group_name')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();

                if ($selectedGroups->all() !== $requiredGroups->all()) {
                    $validator->errors()->add(
                        'answers.'.$question->id,
                        'Choose one answer from every group before continuing.',
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'Please answer the survey before submitting it.',
            'answers.*.required' => 'Choose an answer before continuing.',
            'answers.*.max' => 'You selected more answers than this question allows.',
            'answers.*.size' => 'Choose one answer from every group before continuing.',
        ];
    }

    /**
     * @return Collection<int, SurveyQuestion>
     */
    private function questions(): Collection
    {
        $version = $this->version();

        if (! $version) {
            return new Collection;
        }

        return $this->surveyQuestions ??= $version->questions()
            ->where('is_active', true)
            ->whereHas('surveySection', fn ($query) => $query->where('is_active', true))
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->ordered()
            ->get();
    }

    private function version(): ?SurveyVersion
    {
        return $this->surveyVersion ??= SurveyVersion::query()
            ->where('number', $this->integer('survey_version'))
            ->where('status', '!=', SurveyVersion::STATUS_DRAFT)
            ->first();
    }
}
