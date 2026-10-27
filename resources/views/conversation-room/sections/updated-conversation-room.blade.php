<section class="survey-step" x-cloak x-show="currentStep === 3" data-survey-step="3" aria-labelledby="question-4-title">
    <div class="survey-question-heading">
        <p>The updated Conversation Room</p>
        <div>
            <span class="survey-question-number">04</span>
            <div>
                <h2 id="question-4-title" data-question-title tabindex="-1">Have you tried the updated Conversation Room?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $updatedRoomOptions = [
            ['label' => 'Yes, several times.', 'icon' => 'check-circle', 'tone' => 'green'],
            ['label' => 'Yes, once.', 'icon' => 'check', 'tone' => 'blue'],
            ['label' => 'No, not yet.', 'icon' => 'clock', 'tone' => 'orange'],
            ['label' => "I wasn't aware it had been updated.", 'icon' => 'info', 'tone' => 'purple'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Experience with the updated Conversation Room</legend>
        @foreach ($updatedRoomOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="updated_room_use" value="{{ $option['label'] }}" x-model="answers.updatedRoomUse">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 4" data-survey-step="4" aria-labelledby="question-5-title">
    <div class="survey-question-heading">
        <p>The updated Conversation Room</p>
        <div>
            <span class="survey-question-number">05</span>
            <div>
                <h2 id="question-5-title" data-question-title tabindex="-1">How easy is it to use the updated Conversation Room?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $easeOfUseOptions = [
            ['label' => 'Very easy', 'icon' => 'sparkles', 'tone' => 'green'],
            ['label' => 'Somewhat easy', 'icon' => 'smile', 'tone' => 'blue'],
            ['label' => 'Somewhat difficult', 'icon' => 'gauge', 'tone' => 'orange'],
            ['label' => 'Very difficult', 'icon' => 'alert-circle', 'tone' => 'red'],
            ['label' => 'Not sure', 'icon' => 'help-circle', 'tone' => 'neutral'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Ease of use</legend>
        @foreach ($easeOfUseOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="ease_of_use" value="{{ $option['label'] }}" x-model="answers.easeOfUse">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 5" data-survey-step="5" aria-labelledby="question-6-title">
    <div class="survey-question-heading">
        <p>The updated Conversation Room</p>
        <div>
            <span class="survey-question-number">06</span>
            <div>
                <h2 id="question-6-title" data-question-title tabindex="-1">How useful are the activities for improving your spoken English?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $activityUsefulnessOptions = [
            ['label' => 'Very useful', 'icon' => 'star', 'tone' => 'green'],
            ['label' => 'Somewhat useful', 'icon' => 'thumbs-up', 'tone' => 'blue'],
            ['label' => 'Not very useful', 'icon' => 'minus', 'tone' => 'orange'],
            ['label' => 'Not useful at all', 'icon' => 'thumbs-down', 'tone' => 'red'],
            ['label' => 'Not sure yet', 'icon' => 'help-circle', 'tone' => 'neutral'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Usefulness of the activities</legend>
        @foreach ($activityUsefulnessOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="activity_usefulness" value="{{ $option['label'] }}" x-model="answers.activityUsefulness">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 6" data-survey-step="6" aria-labelledby="question-7-title">
    <div class="survey-question-heading">
        <p>The updated Conversation Room</p>
        <div>
            <span class="survey-question-number">07</span>
            <div>
                <h2 id="question-7-title" data-question-title tabindex="-1">How comfortable do you feel speaking during a Conversation Room session?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $speakingComfortOptions = [
            ['label' => 'Very comfortable', 'icon' => 'microphone', 'tone' => 'green'],
            ['label' => 'Somewhat comfortable', 'icon' => 'message-circle', 'tone' => 'blue'],
            ['label' => 'Somewhat uncomfortable', 'icon' => 'message-circle', 'tone' => 'orange'],
            ['label' => 'Very uncomfortable', 'icon' => 'microphone-off', 'tone' => 'red'],
            ['label' => 'It depends on the group or activity.', 'icon' => 'users', 'tone' => 'purple'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Comfort speaking during a session</legend>
        @foreach ($speakingComfortOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="speaking_comfort" value="{{ $option['label'] }}" x-model="answers.speakingComfort">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 7" data-survey-step="7" aria-labelledby="question-8-title">
    <div class="survey-question-heading">
        <p>The updated Conversation Room</p>
        <div>
            <span class="survey-question-number">08</span>
            <div>
                <h2 id="question-8-title" data-question-title tabindex="-1">Which session format would you prefer?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $sessionFormatOptions = [
            ['label' => 'Small-group conversations', 'icon' => 'users', 'tone' => 'blue'],
            ['label' => 'Pair speaking activities', 'icon' => 'user-round', 'tone' => 'green'],
            ['label' => 'Teacher-led discussion with feedback', 'icon' => 'message-circle', 'tone' => 'purple'],
            ['label' => 'Interactive speaking games and challenges', 'icon' => 'gamepad', 'tone' => 'orange'],
            ['label' => 'A combination of formats', 'icon' => 'layout-grid', 'tone' => 'blue'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Preferred session format</legend>
        @foreach ($sessionFormatOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="session_format" value="{{ $option['label'] }}" x-model="answers.sessionFormat">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>
