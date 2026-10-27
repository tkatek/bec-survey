<section class="survey-step" x-cloak x-show="currentStep === 8" data-survey-step="8" aria-labelledby="question-9-title">
    <div class="survey-question-heading">
        <p>Your preferences</p>
        <div>
            <span class="survey-question-number">09</span>
            <div>
                <h2 id="question-9-title" data-question-title tabindex="-1">What changes would encourage you to attend more often?</h2>
                <span>Select up to three options.</span>
            </div>
        </div>
    </div>

    <div class="survey-selection-count" aria-live="polite">
        <span x-text="answers.encouragementChanges.length"></span> of 3 selected
    </div>

    @php
        $encouragementOptions = [
            ['label' => 'More real-life conversation topics', 'icon' => 'message-circle', 'tone' => 'blue'],
            ['label' => 'Smaller groups and more speaking time', 'icon' => 'users', 'tone' => 'green'],
            ['label' => 'Games, role-plays and interactive challenges', 'icon' => 'gamepad', 'tone' => 'orange'],
            ['label' => 'More personalised feedback from teachers', 'icon' => 'microphone', 'tone' => 'purple'],
            ['label' => 'Topics organised by English level', 'icon' => 'layers', 'tone' => 'blue'],
            ['label' => 'Easier navigation and fewer technical problems', 'icon' => 'compass', 'tone' => 'green'],
            ['label' => 'More flexible session times', 'icon' => 'clock', 'tone' => 'orange'],
            ['label' => 'Clear learning goals and progress tracking', 'icon' => 'target', 'tone' => 'purple'],
            ['label' => 'More opportunities to choose topics', 'icon' => 'hand-pointer', 'tone' => 'blue'],
            ['label' => 'Friendly sessions for shy learners', 'icon' => 'shield', 'tone' => 'green'],
        ];
    @endphp

    <fieldset class="survey-options survey-options--compact">
        <legend class="sr-only">Changes that would encourage attendance</legend>
        @foreach ($encouragementOptions as $option)
            <label
                class="survey-option survey-option--check"
                x-show="isOptionVisible(8, {{ $loop->index }})"
            >
                <input
                    class="survey-option-input"
                    type="checkbox"
                    name="encouragement_changes[]"
                    value="{{ $option['label'] }}"
                    x-model="answers.encouragementChanges"
                    @change.stop="limitSelections('encouragementChanges', 3, $event)"
                >
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 9" data-survey-step="9" aria-labelledby="question-10-title">
    <div class="survey-question-heading">
        <p>Your preferences</p>
        <div>
            <span class="survey-question-number">10</span>
            <div>
                <h2 id="question-10-title" data-question-title tabindex="-1">When would you prefer to attend live speaking sessions?</h2>
                <span>Choose a time of day and your time zone.</span>
            </div>
        </div>
    </div>

    @php
        $sessionTimeOptions = [
            ['label' => 'Morning (6 AM–12 PM)', 'icon' => 'sun', 'tone' => 'orange'],
            ['label' => 'Afternoon (12–5 PM)', 'icon' => 'sunset', 'tone' => 'blue'],
            ['label' => 'Evening (5–9 PM)', 'icon' => 'moon', 'tone' => 'purple'],
            ['label' => 'Late evening (after 9 PM)', 'icon' => 'star', 'tone' => 'purple'],
            ['label' => 'My availability changes.', 'icon' => 'refresh', 'tone' => 'green'],
        ];
        $timeZoneOptions = [
            ['label' => 'Eastern', 'tone' => 'blue'],
            ['label' => 'Central', 'tone' => 'green'],
            ['label' => 'Mountain', 'tone' => 'orange'],
            ['label' => 'Pacific', 'tone' => 'purple'],
            ['label' => 'Other', 'tone' => 'neutral'],
        ];
    @endphp

    <div class="survey-split-fields">
        <fieldset class="survey-field-group">
            <legend>Preferred time</legend>
            <div class="survey-options survey-options--compact">
                @foreach ($sessionTimeOptions as $option)
                    <label class="survey-option">
                        <input class="survey-option-input" type="radio" name="preferred_session_time" value="{{ $option['label'] }}" x-model="answers.preferredSessionTime">
                        <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                        </span>
                        <span class="survey-option-copy">{{ $option['label'] }}</span>
                        <span class="survey-option-control" aria-hidden="true"></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="survey-field-group">
            <legend>Time zone</legend>
            <div class="survey-options survey-options--compact">
                @foreach ($timeZoneOptions as $option)
                    <label class="survey-option">
                        <input class="survey-option-input" type="radio" name="time_zone" value="{{ $option['label'] }}" x-model="answers.timeZone">
                        <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><use href="#survey-icon-globe"></use></svg>
                        </span>
                        <span class="survey-option-copy">{{ $option['label'] }}</span>
                        <span class="survey-option-control" aria-hidden="true"></span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    </div>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 10" data-survey-step="10" aria-labelledby="question-11-title">
    <div class="survey-question-heading">
        <p>Your preferences</p>
        <div>
            <span class="survey-question-number">11</span>
            <div>
                <h2 id="question-11-title" data-question-title tabindex="-1">Which days usually work best for you?</h2>
                <span>Select all that apply.</span>
            </div>
        </div>
    </div>

    @php
        $preferredDayOptions = [
            ['label' => 'Monday', 'tone' => 'blue'],
            ['label' => 'Tuesday', 'tone' => 'green'],
            ['label' => 'Wednesday', 'tone' => 'orange'],
            ['label' => 'Thursday', 'tone' => 'purple'],
            ['label' => 'Friday', 'tone' => 'blue'],
            ['label' => 'Saturday', 'tone' => 'green'],
            ['label' => 'Sunday', 'tone' => 'orange'],
        ];
    @endphp

    <fieldset class="survey-options survey-options--days">
        <legend class="sr-only">Preferred days</legend>
        @foreach ($preferredDayOptions as $option)
            <label
                class="survey-option survey-option--check"
                x-show="isOptionVisible(10, {{ $loop->index }})"
            >
                <input class="survey-option-input" type="checkbox" name="preferred_days[]" value="{{ $option['label'] }}" x-model="answers.preferredDays">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-calendar-days"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 11" data-survey-step="11" aria-labelledby="question-12-title">
    <div class="survey-question-heading">
        <p>Your preferences</p>
        <div>
            <span class="survey-question-number">12</span>
            <div>
                <h2 id="question-12-title" data-question-title tabindex="-1">What is your ideal session length?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $sessionLengthOptions = [
            ['label' => '15 minutes', 'tone' => 'green'],
            ['label' => '30 minutes', 'tone' => 'blue'],
            ['label' => '45 minutes', 'tone' => 'orange'],
            ['label' => '60 minutes', 'tone' => 'purple'],
        ];
    @endphp

    <fieldset class="survey-options survey-options--two-columns">
        <legend class="sr-only">Ideal session length</legend>
        @foreach ($sessionLengthOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="session_length" value="{{ $option['label'] }}" x-model="answers.sessionLength">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-timer"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>
