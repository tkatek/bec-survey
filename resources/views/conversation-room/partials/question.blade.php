<section
    class="survey-step"
    x-cloak
    x-show="currentStep === {{ $step }}"
    data-survey-step="{{ $step }}"
    aria-labelledby="question-{{ $question->id }}-title"
>
    <div class="survey-question-heading">
        <p>{{ $question->section }}</p>
        <div>
            <span class="survey-question-number">{{ str_pad((string) ($step + 1), 2, '0', STR_PAD_LEFT) }}</span>
            <div>
                <h2 id="question-{{ $question->id }}-title" data-question-title tabindex="-1">{{ $question->prompt }}</h2>
                @if ($question->help_text)
                    <span>{{ $question->help_text }}</span>
                @endif
            </div>
        </div>
    </div>

    @if ($question->type === \App\Models\SurveyQuestion::TYPE_LONG_TEXT)
        <div class="survey-text-response">
            <label for="answer-{{ $question->id }}">Your answer</label>
            <textarea
                id="answer-{{ $question->id }}"
                name="answers[{{ $question->id }}]"
                rows="7"
                maxlength="1000"
                placeholder="Share the change that would improve your Conversation Room experience..."
                x-model="answers['{{ $question->id }}']"
            ></textarea>
            <span><span x-text="String(answers['{{ $question->id }}'] ?? '').length"></span> / 1000</span>
        </div>
    @elseif ($question->type === \App\Models\SurveyQuestion::TYPE_GROUPED_CHOICE)
        <div class="survey-split-fields">
            @foreach ($question->options->groupBy('group_name') as $group => $options)
                <fieldset class="survey-field-group">
                    <legend>{{ $group }}</legend>
                    <div class="survey-options survey-options--compact">
                        @foreach ($options as $option)
                            <label class="survey-option">
                                <input
                                    class="survey-option-input"
                                    type="radio"
                                    name="answers[{{ $question->id }}][{{ $group }}]"
                                    value="{{ $option->id }}"
                                    x-model="answers['{{ $question->id }}']['{{ $group }}']"
                                >
                                <span class="survey-option-marker" data-tone="{{ $option->tone }}" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option->icon }}"></use></svg>
                                </span>
                                <span class="survey-option-copy">{{ $option->label }}</span>
                                <span class="survey-option-control" aria-hidden="true"></span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
    @else
        @if ($question->type === \App\Models\SurveyQuestion::TYPE_MULTIPLE_CHOICE && $question->max_selections)
            <div class="survey-selection-count" aria-live="polite">
                <span x-text="(answers['{{ $question->id }}'] ?? []).length"></span> of {{ $question->max_selections }} selected
            </div>
        @endif

        <fieldset @class([
            'survey-options',
            'survey-options--compact' => $question->options->count() > 7,
            'survey-options--days' => $question->key === 'preferred_days',
            'survey-options--two-columns' => $question->options->count() === 4,
        ])>
            <legend class="sr-only">{{ $question->prompt }}</legend>
            @foreach ($question->options as $option)
                <label @class(['survey-option', 'survey-option--check' => $question->type === \App\Models\SurveyQuestion::TYPE_MULTIPLE_CHOICE])>
                    <input
                        class="survey-option-input"
                        type="{{ $question->type === \App\Models\SurveyQuestion::TYPE_MULTIPLE_CHOICE ? 'checkbox' : 'radio' }}"
                        name="answers[{{ $question->id }}]{{ $question->type === \App\Models\SurveyQuestion::TYPE_MULTIPLE_CHOICE ? '[]' : '' }}"
                        value="{{ $option->id }}"
                        x-model="answers['{{ $question->id }}']"
                        @if ($question->type === \App\Models\SurveyQuestion::TYPE_MULTIPLE_CHOICE && $question->max_selections)
                            @change.stop="limitSelections('{{ $question->id }}', {{ $question->max_selections }}, $event)"
                        @endif
                    >
                    <span class="survey-option-marker" data-tone="{{ $option->tone }}" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option->icon }}"></use></svg>
                    </span>
                    <span class="survey-option-copy">{{ $option->label }}</span>
                    <span class="survey-option-control" aria-hidden="true"></span>
                </label>
            @endforeach
        </fieldset>
    @endif
</section>
