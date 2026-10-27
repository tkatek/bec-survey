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

    <fieldset class="survey-options survey-options--compact">
        <legend class="sr-only">Changes that would encourage attendance</legend>
        @foreach (['More real-life conversation topics', 'Smaller groups and more speaking time', 'Games, role-plays and interactive challenges', 'More personalised feedback from teachers', 'Topics organised by English level', 'Easier navigation and fewer technical problems', 'More flexible session times', 'Clear learning goals and progress tracking', 'More opportunities to choose topics', 'Friendly sessions for shy learners'] as $option)
            <label class="survey-option survey-option--check">
                <input
                    class="survey-option-input"
                    type="checkbox"
                    name="encouragement_changes[]"
                    value="{{ $option }}"
                    x-model="answers.encouragementChanges"
                    @change.stop="limitSelections('encouragementChanges', 3, $event)"
                >
                <span class="survey-option-marker" aria-hidden="true">{{ $loop->iteration }}</span>
                <span class="survey-option-copy">{{ $option }}</span>
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

    <div class="survey-split-fields">
        <fieldset class="survey-field-group">
            <legend>Preferred time</legend>
            <div class="survey-options survey-options--compact">
                @foreach (['Morning (6 AM–12 PM)', 'Afternoon (12–5 PM)', 'Evening (5–9 PM)', 'Late evening (after 9 PM)', 'My availability changes.'] as $option)
                    <label class="survey-option">
                        <input class="survey-option-input" type="radio" name="preferred_session_time" value="{{ $option }}" x-model="answers.preferredSessionTime">
                        <span class="survey-option-marker" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="survey-option-copy">{{ $option }}</span>
                        <span class="survey-option-control" aria-hidden="true"></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="survey-field-group">
            <legend>Time zone</legend>
            <div class="survey-options survey-options--compact">
                @foreach (['Eastern', 'Central', 'Mountain', 'Pacific', 'Other'] as $option)
                    <label class="survey-option">
                        <input class="survey-option-input" type="radio" name="time_zone" value="{{ $option }}" x-model="answers.timeZone">
                        <span class="survey-option-marker" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="survey-option-copy">{{ $option }}</span>
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

    <fieldset class="survey-options survey-options--days">
        <legend class="sr-only">Preferred days</legend>
        @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $option)
            <label class="survey-option survey-option--check">
                <input class="survey-option-input" type="checkbox" name="preferred_days[]" value="{{ $option }}" x-model="answers.preferredDays">
                <span class="survey-option-marker" aria-hidden="true">{{ $loop->iteration }}</span>
                <span class="survey-option-copy">{{ $option }}</span>
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

    <fieldset class="survey-options survey-options--two-columns">
        <legend class="sr-only">Ideal session length</legend>
        @foreach (['15 minutes', '30 minutes', '45 minutes', '60 minutes'] as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="session_length" value="{{ $option }}" x-model="answers.sessionLength">
                <span class="survey-option-marker" aria-hidden="true">{{ $loop->iteration }}</span>
                <span class="survey-option-copy">{{ $option }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>
