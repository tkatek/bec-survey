<?php

namespace App\Http\Requests\Admin;

use App\Models\SurveyVersion;
use App\Support\AnalyticsFilter;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AnalyticsFilterRequest extends FormRequest
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
        return [
            'preset' => ['required', Rule::in(['today', 'last7', 'month', 'custom'])],
            'start' => ['nullable', 'required_if:preset,custom', 'date_format:Y-m-d'],
            'end' => ['nullable', 'required_if:preset,custom', 'date_format:Y-m-d'],
            'version' => [
                'required',
                'integer',
                Rule::exists('survey_versions', 'number')->where(
                    fn ($query) => $query->where('status', '!=', SurveyVersion::STATUS_DRAFT),
                ),
            ],
            'tab' => ['nullable', Rule::in(['selected', 'feedback'])],
            'section' => ['nullable', 'string', 'max:120'],
            'question' => ['nullable', 'string', 'max:80'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['original', 'count'])],
            'theme' => ['nullable', 'string', Rule::exists('feedback_themes', 'key')],
            'alert' => ['nullable', 'integer', 'exists:survey_alerts,id'],
            'snapshot' => ['nullable', 'uuid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->string('preset')->toString() !== 'custom' || $validator->errors()->isNotEmpty()) {
                return;
            }

            $timezone = (string) config('survey.reporting_timezone', config('app.timezone'));
            $start = CarbonImmutable::parse($this->string('start')->toString(), $timezone)->startOfDay();
            $end = CarbonImmutable::parse($this->string('end')->toString(), $timezone)->startOfDay();
            $today = CarbonImmutable::now($timezone)->startOfDay();

            if ($end->lessThan($start)) {
                $validator->errors()->add('end', 'The end date must be on or after the start date.');
            }

            if ($start->greaterThan($today) || $end->greaterThan($today)) {
                $validator->errors()->add('end', 'Future reporting dates are not available.');
            }

            $maximumDays = (int) config('survey.analytics.maximum_custom_range_days', 366);

            if ($start->diffInDays($end) + 1 > $maximumDays) {
                $validator->errors()->add('end', "Choose a range of {$maximumDays} days or fewer.");
            }
        });
    }

    public function filter(): AnalyticsFilter
    {
        /** @var array{preset: string, start?: string|null, end?: string|null, version: int} $validated */
        $validated = $this->validated();

        return AnalyticsFilter::fromValidated($validated);
    }

    protected function prepareForValidation(): void
    {
        $activeVersion = SurveyVersion::query()->active()->value('number');

        $this->merge([
            'preset' => $this->input('preset', 'month'),
            'version' => $this->input('version', $activeVersion),
        ]);
    }
}
