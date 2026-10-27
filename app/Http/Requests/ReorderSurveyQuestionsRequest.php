<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReorderSurveyQuestionsRequest extends FormRequest
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
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.section_stable_id' => ['required', 'uuid', 'distinct:strict'],
            'groups.*.question_stable_ids' => ['present', 'array'],
            'groups.*.question_stable_ids.*' => ['required', 'uuid'],
        ];
    }
}
