<?php

namespace App\Http\Requests;

use App\Models\SurveySection;
use App\Models\SurveyVersion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSurveySectionRequest extends FormRequest
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
        $version = SurveyVersion::query()->draft()->first() ?? SurveyVersion::query()->active()->first();
        $sourceSection = $this->route('section');
        $targetSectionId = $sourceSection instanceof SurveySection && $version
            ? SurveySection::query()
                ->where('survey_version_id', $version->id)
                ->where('stable_id', $sourceSection->stable_id)
                ->value('id')
            : null;

        return [
            'title' => [
                'required',
                'string',
                'max:120',
                Rule::unique('survey_sections', 'title')
                    ->where(fn ($query) => $query
                        ->when($version, fn ($versionQuery) => $versionQuery->where('survey_version_id', $version->id))
                        ->whereNull('deleted_at'))
                    ->ignore($targetSectionId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:1', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
