<section class="survey-step" x-cloak x-show="currentStep === 0" data-survey-step="0" aria-labelledby="question-1-title">
    <div class="survey-question-heading">
        <p>Attendance and participation</p>
        <div>
            <span class="survey-question-number">01</span>
            <div>
                <h2 id="question-1-title" data-question-title tabindex="-1">How often have you attended the Conversation Room in the past month?</h2>
                <span>Choose the answer that best describes the past four weeks.</span>
            </div>
        </div>
    </div>

    @php
        $attendanceOptions = [
            ['label' => 'Never', 'icon' => 'ban', 'tone' => 'red'],
            ['label' => 'Once or twice', 'icon' => 'calendar', 'tone' => 'blue'],
            ['label' => 'About once a week', 'icon' => 'calendar-days', 'tone' => 'green'],
            ['label' => 'Several times a week', 'icon' => 'calendar', 'tone' => 'orange'],
            ['label' => 'Almost every day', 'icon' => 'calendar-star', 'tone' => 'purple'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Attendance frequency</legend>
        @foreach ($attendanceOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="attendance_frequency" value="{{ $option['label'] }}" x-model="answers.attendanceFrequency">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 1" data-survey-step="1" aria-labelledby="question-2-title">
    <div class="survey-question-heading">
        <p>Attendance and participation</p>
        <div>
            <span class="survey-question-number">02</span>
            <div>
                <h2 id="question-2-title" data-question-title tabindex="-1">Compared with before the Conversation Room update, how has your attendance changed?</h2>
                <span>Choose one answer.</span>
            </div>
        </div>
    </div>

    @php
        $attendanceChangeOptions = [
            ['label' => 'I attend more often now.', 'icon' => 'trending-up', 'tone' => 'green'],
            ['label' => 'My attendance is about the same.', 'icon' => 'minus', 'tone' => 'blue'],
            ['label' => 'I attend less often now.', 'icon' => 'trending-down', 'tone' => 'orange'],
            ['label' => 'I have stopped attending.', 'icon' => 'stop-circle', 'tone' => 'red'],
            ['label' => "I joined after the update / I didn't attend before.", 'icon' => 'user-plus', 'tone' => 'purple'],
        ];
    @endphp

    <fieldset class="survey-options">
        <legend class="sr-only">Change in attendance</legend>
        @foreach ($attendanceChangeOptions as $option)
            <label class="survey-option">
                <input class="survey-option-input" type="radio" name="attendance_change" value="{{ $option['label'] }}" x-model="answers.attendanceChange">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>

<section class="survey-step" x-cloak x-show="currentStep === 2" data-survey-step="2" aria-labelledby="question-3-title">
    <div class="survey-question-heading">
        <p>Attendance and participation</p>
        <div>
            <span class="survey-question-number">03</span>
            <div>
                <h2 id="question-3-title" data-question-title tabindex="-1">What are the main reasons you attend less often or don't attend?</h2>
                <span>Select all that apply, or continue if this question does not apply to you.</span>
            </div>
        </div>
    </div>

    @php
        $attendanceReasonOptions = [
            ['label' => 'My schedule has changed.', 'icon' => 'calendar', 'tone' => 'red'],
            ['label' => 'The available session times are inconvenient.', 'icon' => 'clock', 'tone' => 'blue'],
            ['label' => "I didn't know when sessions were available.", 'icon' => 'info', 'tone' => 'orange'],
            ['label' => "I haven't been motivated to join.", 'icon' => 'lightbulb', 'tone' => 'purple'],
            ['label' => "I'm nervous about speaking or making mistakes.", 'icon' => 'message-circle', 'tone' => 'green'],
            ['label' => "The topics or activities don't interest me.", 'icon' => 'file-text', 'tone' => 'blue'],
            ['label' => 'The sessions are too easy or too difficult.', 'icon' => 'bar-chart', 'tone' => 'green'],
            ['label' => "I don't get enough opportunities to speak.", 'icon' => 'users', 'tone' => 'purple'],
            ['label' => "The group atmosphere or teaching style doesn't suit me.", 'icon' => 'home', 'tone' => 'red'],
            ['label' => 'I have experienced technical problems.', 'icon' => 'wifi', 'tone' => 'blue'],
            ['label' => 'I prefer other ways of practising English.', 'icon' => 'book-open', 'tone' => 'orange'],
            ['label' => 'Other personal reasons.', 'icon' => 'ellipsis', 'tone' => 'neutral'],
        ];
    @endphp

    <fieldset class="survey-options survey-options--compact">
        <legend class="sr-only">Reasons for attending less often</legend>
        @foreach ($attendanceReasonOptions as $option)
            <label
                class="survey-option survey-option--check"
                x-show="isOptionVisible(2, {{ $loop->index }})"
            >
                <input class="survey-option-input" type="checkbox" name="attendance_reasons[]" value="{{ $option['label'] }}" x-model="answers.attendanceReasons">
                <span class="survey-option-marker" data-tone="{{ $option['tone'] }}" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><use href="#survey-icon-{{ $option['icon'] }}"></use></svg>
                </span>
                <span class="survey-option-copy">{{ $option['label'] }}</span>
                <span class="survey-option-control" aria-hidden="true"></span>
            </label>
        @endforeach
    </fieldset>
</section>
