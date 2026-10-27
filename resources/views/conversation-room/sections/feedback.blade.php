<section class="survey-step" x-cloak x-show="currentStep === 12" data-survey-step="12" aria-labelledby="question-13-title">
    <div class="survey-question-heading">
        <p>Your feedback</p>
        <div>
            <span class="survey-question-number">13</span>
            <div>
                <h2 id="question-13-title" data-question-title tabindex="-1">How likely are you to attend the Conversation Room in the next two weeks?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $attendanceLikelihoodOptions = [
            ['label' => 'Very likely', 'icon' => 'star', 'tone' => 'green'],
            ['label' => 'Somewhat likely', 'icon' => 'thumbs-up', 'tone' => 'blue'],
            ['label' => 'Not sure', 'icon' => 'help-circle', 'tone' => 'neutral'],
            ['label' => 'Somewhat unlikely', 'icon' => 'trending-down', 'tone' => 'orange'],
            ['label' => 'Very unlikely', 'icon' => 'thumbs-down', 'tone' => 'red'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Likelihood of attending in the next two weeks</legend>
        @foreach ($attendanceLikelihoodOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="attendance_likelihood" value="{{ $option['label'] }}" x-model="answers.attendanceLikelihood">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 13" data-survey-step="13" aria-labelledby="question-14-title">
    <div class="survey-question-heading">
        <p>Your feedback</p>
        <div>
            <span class="survey-question-number">14</span>
            <div>
                <h2 id="question-14-title" data-question-title tabindex="-1">If you could change one thing about the Conversation Room, what would it be?</h2>
                <span>This final answer is optional. Share as much or as little as you like.</span>
            </div>
        </div>
    </div>

    <div class="survey-text-response">
        <label for="one-change">Your suggestion</label>
        <textarea
            id="one-change"
            name="one_change"
            rows="7"
            maxlength="1000"
            placeholder="For example: more evening sessions, smaller groups, or more time to speak..."
            x-model="answers.oneChange"
        ></textarea>
        <span><span x-text="answers.oneChange.length"></span> / 1000</span>
    </div>
</section>
