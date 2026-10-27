<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#071326">

        <title>{{ __('Conversation Room Feedback Survey | Boston English Center') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="preload" as="image" href="{{ asset('images/conversation-room/conversation-room-background.webp') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="survey-page">
        <svg class="survey-icon-sprite" aria-hidden="true">
            <symbol id="survey-icon-ban" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m6 6 12 12"></path></symbol>
            <symbol id="survey-icon-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></symbol>
            <symbol id="survey-icon-calendar-days" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01"></path></symbol>
            <symbol id="survey-icon-calendar-star" viewBox="0 0 24 24"><path d="M8 3v3m8-3v3M3 10h18M5 5h14a2 2 0 0 1 2 2v5"></path><path d="m17 14 .85 1.72 1.9.28-1.38 1.34.33 1.9L17 18.35l-1.7.89.33-1.9L14.25 16l1.9-.28L17 14Z"></path><path d="M13 21H5a2 2 0 0 1-2-2V7"></path></symbol>
            <symbol id="survey-icon-trending-up" viewBox="0 0 24 24"><path d="m3 17 6-6 4 4 8-8"></path><path d="M14 7h7v7"></path></symbol>
            <symbol id="survey-icon-minus" viewBox="0 0 24 24"><path d="M5 12h14"></path></symbol>
            <symbol id="survey-icon-trending-down" viewBox="0 0 24 24"><path d="m3 7 6 6 4-4 8 8"></path><path d="M14 17h7v-7"></path></symbol>
            <symbol id="survey-icon-stop-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><rect x="9" y="9" width="6" height="6" rx="1"></rect></symbol>
            <symbol id="survey-icon-user-plus" viewBox="0 0 24 24"><path d="M15 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-3A4.5 4.5 0 0 0 3 18.5V20M9 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM19 8v6m-3-3h6"></path></symbol>
            <symbol id="survey-icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></symbol>
            <symbol id="survey-icon-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5m0-8h.01"></path></symbol>
            <symbol id="survey-icon-lightbulb" viewBox="0 0 24 24"><path d="M9 18h6m-5 3h4m2.5-6.5A7 7 0 1 0 7.5 14.5C8.5 15.4 9 16.3 9 18h6c0-1.7.5-2.6 1.5-3.5Z"></path></symbol>
            <symbol id="survey-icon-message-circle" viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-9.3 8.45 9.3 9.3 0 0 1-3.9-.98L3 20l1.22-3.48A8.5 8.5 0 1 1 21 11.5Z"></path><path d="M8 11h.01M12 11h.01M16 11h.01"></path></symbol>
            <symbol id="survey-icon-file-text" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path><path d="M14 2v6h6M8 13h8M8 17h6"></path></symbol>
            <symbol id="survey-icon-bar-chart" viewBox="0 0 24 24"><path d="M4 19v-5m6 5V9m6 10V5m4 14H2"></path></symbol>
            <symbol id="survey-icon-users" viewBox="0 0 24 24"><path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20m6.5-10A3.5 3.5 0 1 0 9.5 3a3.5 3.5 0 0 0 0 7Zm7.5 2a3 3 0 1 0 0-6m4 14v-1.5a4 4 0 0 0-3-3.87"></path></symbol>
            <symbol id="survey-icon-home" viewBox="0 0 24 24"><path d="m3 11 9-8 9 8"></path><path d="M5 10v10h14V10M9 20v-6h6v6"></path></symbol>
            <symbol id="survey-icon-wifi" viewBox="0 0 24 24"><path d="M5 12.5a10 10 0 0 1 14 0M8 16a5.7 5.7 0 0 1 8 0M11.5 19.5h1"></path></symbol>
            <symbol id="survey-icon-book-open" viewBox="0 0 24 24"><path d="M3 5.5A3.5 3.5 0 0 1 6.5 2H11v18H6.5A3.5 3.5 0 0 0 3 23Z"></path><path d="M21 5.5A3.5 3.5 0 0 0 17.5 2H13v18h4.5A3.5 3.5 0 0 1 21 23Z"></path></symbol>
            <symbol id="survey-icon-ellipsis" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle></symbol>
            <symbol id="survey-icon-check-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.5 2.5L16.5 8"></path></symbol>
            <symbol id="survey-icon-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></symbol>
            <symbol id="survey-icon-sparkles" viewBox="0 0 24 24"><path d="m12 3 1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2ZM6 14l.8 2.2L9 17l-2.2.8L6 20l-.8-2.2L3 17l2.2-.8ZM18 14l.8 2.2L21 17l-2.2.8L18 20l-.8-2.2L15 17l2.2-.8Z"></path></symbol>
            <symbol id="survey-icon-smile" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"></path></symbol>
            <symbol id="survey-icon-gauge" viewBox="0 0 24 24"><path d="M4.5 18a9 9 0 1 1 15 0"></path><path d="m12 14 4-4M7 18h10"></path></symbol>
            <symbol id="survey-icon-alert-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6m0 4h.01"></path></symbol>
            <symbol id="survey-icon-help-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M9.8 9a2.4 2.4 0 1 1 3.9 1.9c-.9.6-1.7 1.1-1.7 2.6M12 17h.01"></path></symbol>
            <symbol id="survey-icon-star" viewBox="0 0 24 24"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-2.9-5.6 2.9 1.1-6.2L3 9.6l6.2-.9Z"></path></symbol>
            <symbol id="survey-icon-thumbs-up" viewBox="0 0 24 24"><path d="M7 10v10H3V10Zm0 9h10.2a2 2 0 0 0 1.95-1.56l1.35-6A2 2 0 0 0 18.55 9H14l.7-3.45A2 2 0 0 0 12.74 3L7 10Z"></path></symbol>
            <symbol id="survey-icon-thumbs-down" viewBox="0 0 24 24"><path d="M7 14V4H3v10Zm0-9h10.2a2 2 0 0 1 1.95 1.56l1.35 6A2 2 0 0 1 18.55 15H14l.7 3.45A2 2 0 0 1 12.74 21L7 14Z"></path></symbol>
            <symbol id="survey-icon-microphone" viewBox="0 0 24 24"><rect x="9" y="3" width="6" height="11" rx="3"></rect><path d="M5 11a7 7 0 0 0 14 0M12 18v3M8 21h8"></path></symbol>
            <symbol id="survey-icon-microphone-off" viewBox="0 0 24 24"><path d="m3 3 18 18M9 5.5V11a3 3 0 0 0 4.7 2.5M15 9V6a3 3 0 0 0-5.6-1.5M5 11a7 7 0 0 0 11.2 5.6M19 11a7 7 0 0 1-.4 2.3M12 18v3M8 21h8"></path></symbol>
            <symbol id="survey-icon-user-round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></symbol>
            <symbol id="survey-icon-gamepad" viewBox="0 0 24 24"><path d="M8 6h8a5 5 0 0 1 4.8 6.4l-1.4 4.8a2.5 2.5 0 0 1-4.2 1L13.5 16h-3l-1.7 2.2a2.5 2.5 0 0 1-4.2-1l-1.4-4.8A5 5 0 0 1 8 6Z"></path><path d="M8 10v4m-2-2h4m6-1h.01m2 2h.01"></path></symbol>
            <symbol id="survey-icon-layout-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></symbol>
            <symbol id="survey-icon-layers" viewBox="0 0 24 24"><path d="m12 2 9 5-9 5-9-5Zm-9 10 9 5 9-5M3 17l9 5 9-5"></path></symbol>
            <symbol id="survey-icon-compass" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="m16 8-2.5 5.5L8 16l2.5-5.5Z"></path></symbol>
            <symbol id="survey-icon-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><circle cx="12" cy="12" r="5"></circle><circle cx="12" cy="12" r="1"></circle></symbol>
            <symbol id="survey-icon-hand-pointer" viewBox="0 0 24 24"><path d="M8 11V7a2 2 0 0 1 4 0v4-6a2 2 0 0 1 4 0v6-3a2 2 0 0 1 4 0v6c0 4-3 7-7 7h-1c-2 0-3.5-.8-4.8-2.4L4 14.5a2 2 0 0 1 3-2.6Z"></path></symbol>
            <symbol id="survey-icon-shield" viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.8 2.95 8.16 7 10 4.05-1.84 7-5.2 7-10V6Z"></path><path d="m9 12 2 2 4-4"></path></symbol>
            <symbol id="survey-icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></symbol>
            <symbol id="survey-icon-sunset" viewBox="0 0 24 24"><path d="M4 18h16M6 14a6 6 0 0 1 12 0M12 3v3M4.9 7.9 7 10m12.1-2.1L17 10"></path></symbol>
            <symbol id="survey-icon-moon" viewBox="0 0 24 24"><path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 8.5 8.5 0 1 0 20 15.5Z"></path></symbol>
            <symbol id="survey-icon-refresh" viewBox="0 0 24 24"><path d="M20 7v5h-5M4 17v-5h5"></path><path d="M18.5 12A7 7 0 0 0 6.2 7.5L4 12m16 0-2.2 4.5A7 7 0 0 1 5.5 12"></path></symbol>
            <symbol id="survey-icon-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path></symbol>
            <symbol id="survey-icon-timer" viewBox="0 0 24 24"><circle cx="12" cy="13" r="8"></circle><path d="M12 9v4l2 2M9 2h6M12 5V2"></path></symbol>
            <symbol id="survey-icon-lock" viewBox="0 0 24 24"><path d="M7 10V8a5 5 0 0 1 10 0v2m-9 0h8a2 2 0 0 1 2 2v7H6v-7a2 2 0 0 1 2-2Z"></path></symbol>
            <symbol id="survey-icon-heart" viewBox="0 0 24 24"><path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 22l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"></path></symbol>
        </svg>

        <main class="survey-layout" x-data="conversationRoomSurvey(@js($surveyConfiguration))">
            <aside class="survey-intro" data-survey-intro>
                <div class="survey-intro-brand" data-intro-reveal>
                    <img src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                </div>

                <div class="survey-intro-mobile-status" data-intro-reveal aria-label="Estimated completion time: 4 to 5 minutes">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><use href="#survey-icon-clock"></use></svg>
                    <span>4–5 minutes</span>
                </div>

                <div class="survey-intro-copy" data-intro-reveal>
                    <p>Your voice shapes the room.</p>
                    <h1>Help us make speaking English <span>feel more natural.</span></h1>
                    <strong>Tell us what works, what gets in the way, and what would make you want to join more often.</strong>
                </div>

                <ul class="survey-intro-points" data-intro-reveal aria-label="Conversation Room benefits">
                    <li>
                        <span><svg viewBox="0 0 24 24"><use href="#survey-icon-microphone"></use></svg></span>
                        Speak with confidence
                    </li>
                    <li>
                        <span><svg viewBox="0 0 24 24"><use href="#survey-icon-users"></use></svg></span>
                        Meet other learners
                    </li>
                    <li>
                        <span><svg viewBox="0 0 24 24"><use href="#survey-icon-heart"></use></svg></span>
                        Shape future sessions
                    </li>
                </ul>

                <img
                    class="survey-intro-photo"
                    src="{{ asset('images/conversation-room/conversation-room-background.webp') }}"
                    alt="An English learner holding a notebook in a bright conversation space"
                >
                <div class="survey-intro-photo-fade" aria-hidden="true"></div>
            </aside>

            <section class="survey-workspace" aria-label="Conversation Room feedback survey">
                <div class="survey-card" data-survey-card>
                    <header class="survey-card-header">
                        <div>
                            <p class="survey-card-title">Conversation Room Feedback Survey</p>
                            <p class="survey-card-description">Your honest feedback will improve the learning experience.</p>
                        </div>

                        <div class="survey-card-status">
                            <span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><use href="#survey-icon-lock"></use></svg>
                                Anonymous survey
                            </span>
                            <i aria-hidden="true"></i>
                            <span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><use href="#survey-icon-clock"></use></svg>
                                4–5 minutes
                            </span>
                        </div>
                    </header>

                    <ol class="survey-section-track" aria-label="Survey sections" x-show="!isComplete">
                        @foreach ($sections as $section)
                            <li :class="{ 'is-active': isSectionActive({{ $loop->index }}), 'is-complete': isSectionComplete({{ $loop->index }}) }">
                                <span>{{ $loop->iteration }}</span>
                                <em>{{ $section }}</em>
                            </li>
                        @endforeach
                    </ol>

                    <div class="survey-progress" x-show="!isComplete">
                        <div class="survey-progress-copy">
                            <span>Question <strong x-text="currentStep + 1"></strong> of {{ $questions->count() }}</span>
                            <span><strong x-text="progressPercentage"></strong>% complete</span>
                        </div>
                        <div class="survey-progress-track" role="progressbar" :aria-valuenow="progressPercentage" aria-valuemin="0" aria-valuemax="100">
                            <span :style="`width: ${progressPercentage}%`"></span>
                        </div>
                    </div>

                    <form
                        class="survey-form"
                        method="POST"
                        action="{{ route('conversation-room.store') }}"
                        x-ref="surveyForm"
                        :aria-busy="isAnimating.toString()"
                        @submit.prevent="nextQuestion"
                        @change="clearError"
                        @input="clearError"
                        novalidate
                    >
                        @csrf
                        <div class="survey-stage" x-ref="stage">
                            @foreach ($questions as $question)
                                @include('conversation-room.partials.question', ['question' => $question, 'step' => $loop->index])
                            @endforeach

                            <section
                                class="survey-complete"
                                x-cloak
                                x-show="isComplete"
                                data-survey-complete
                                aria-labelledby="survey-complete-title"
                            >
                                <div class="survey-complete-visual" data-success-visual aria-hidden="true">
                                    <span class="survey-complete-ring" data-success-ring></span>
                                    <div class="survey-complete-mark">
                                        <svg viewBox="0 0 24 24">
                                            <path class="survey-complete-check" data-success-check d="m5 12 4 4L19 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p data-success-content>Survey complete</p>
                                <h2 id="survey-complete-title" data-completion-title data-success-content tabindex="-1">
                                    Thank you for sharing your experience.
                                </h2>
                                <span data-success-content>
                                    Your feedback will help Boston English Center create a more useful and welcoming Conversation Room.
                                </span>
                                <div class="survey-complete-actions" data-success-content>
                                    <a href="{{ route('conversation-room.create') }}" class="survey-button survey-button--primary">
                                        Back to home
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg>
                                    </a>
                                    <button type="button" class="survey-button survey-button--secondary" @click="restartSurvey">
                                        Review my answers
                                    </button>
                                </div>
                            </section>
                        </div>

                        <footer class="survey-navigation" x-cloak x-show="!isComplete">
                            <div class="survey-error" aria-live="polite">
                                <span x-show="errorMessage" x-text="errorMessage"></span>
                            </div>

                            <div class="survey-navigation-actions">
                                <button
                                    type="button"
                                    class="survey-button survey-button--secondary"
                                    :class="{ 'is-hidden': currentStep === 0 }"
                                    :disabled="isAnimating || currentStep === 0"
                                    @click="previousQuestion"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m15 18-6-6 6-6" />
                                    </svg>
                                    Back
                                </button>

                                <button type="submit" class="survey-button survey-button--primary" :disabled="isAnimating">
                                    <span x-text="nextButtonLabel"></span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        </footer>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
