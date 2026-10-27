<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#071326">

        <title>{{ __('Conversation Room Feedback Survey | Boston English Center') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
        <link rel="preload" as="image" href="{{ asset('images/conversation-room/conversation-student.webp') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="survey-page">
        <main class="survey-layout" x-data="conversationRoomSurvey">
            <header class="platform-header">
                <a href="{{ route('conversation-room.create') }}" class="platform-brand" aria-label="Boston English Center survey home">
                    <img src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
                </a>

                <div class="platform-context" aria-label="Current area">
                    <span class="platform-context-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M7.5 8.5h9m-9 3.5h6m7.5-.5a8.5 8.5 0 0 1-9.3 8.45 9.3 9.3 0 0 1-3.9-.98L3 20l1.22-3.48A8.5 8.5 0 1 1 21 11.5Z" />
                        </svg>
                    </span>
                    <span>Conversation Room</span>
                </div>

                <div class="platform-status">
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M7 10V8a5 5 0 0 1 10 0v2m-9 0h8a2 2 0 0 1 2 2v7H6v-7a2 2 0 0 1 2-2Z" />
                        </svg>
                        Anonymous
                    </span>
                    <i aria-hidden="true"></i>
                    <span>
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                        </svg>
                        4–5 min
                    </span>
                </div>
            </header>

            <div class="survey-content">
                <aside class="survey-intro" data-survey-intro>
                    <img
                        class="survey-intro-photo"
                        src="{{ asset('images/conversation-room/conversation-student.webp') }}"
                        alt="An English learner holding a notebook in a bright conversation space"
                    >
                    <div class="survey-intro-shade" aria-hidden="true"></div>

                    <div class="survey-intro-copy" data-intro-reveal>
                        <span class="survey-intro-badge">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M16 20v-1.5a4.5 4.5 0 0 0-4.5-4.5h-4A4.5 4.5 0 0 0 3 18.5V20m6.5-10A3.5 3.5 0 1 0 9.5 3a3.5 3.5 0 0 0 0 7Zm7.5 2a3 3 0 1 0 0-6m4 14v-1.5a4 4 0 0 0-3-3.87" />
                            </svg>
                            Built for better conversations
                        </span>
                        <h1>Speak with confidence. Learn together.</h1>
                        <p>Your experience helps us make every Conversation Room session more useful, welcoming, and easy to join.</p>
                    </div>

                    <div class="survey-intro-notes" data-intro-reveal aria-hidden="true">
                        <span>
                            <svg viewBox="0 0 24 24">
                                <path d="m5 13 4 4L19 7" />
                            </svg>
                            Meet new people
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24">
                                <path d="M5 17.5 9.5 13l3 3L19 8m0 0h-5m5 0v5" />
                            </svg>
                            Grow your English
                        </span>
                    </div>
                </aside>

                <section class="survey-workspace" aria-label="Conversation Room feedback survey">
                    <div class="survey-card" data-survey-card>
                        <header class="survey-card-header">
                            <div>
                                <p class="survey-card-title">Conversation Room Feedback Survey</p>
                                <p class="survey-card-description">Your honest feedback will improve the learning experience.</p>
                            </div>

                            <div class="survey-card-privacy">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 3 5 6v5c0 4.8 2.95 8.16 7 10 4.05-1.84 7-5.2 7-10V6l-7-3Z" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>
                                Private and secure
                            </div>
                        </header>

                        <div class="survey-progress" x-show="!isComplete">
                            <div class="survey-progress-copy">
                                <span>Question <strong x-text="currentStep + 1"></strong> of 14</span>
                                <span x-text="`${progressPercentage}%`"></span>
                            </div>
                            <div class="survey-progress-track" role="progressbar" :aria-valuenow="progressPercentage" aria-valuemin="0" aria-valuemax="100">
                                <span :style="`width: ${progressPercentage}%`"></span>
                            </div>
                        </div>

                        <form class="survey-form" @submit.prevent="nextQuestion" @change="clearError" @input="clearError" novalidate>
                            <div class="survey-stage" x-ref="stage">
                                @include('conversation-room.sections.attendance-participation')
                                @include('conversation-room.sections.updated-conversation-room')
                                @include('conversation-room.sections.preferences')
                                @include('conversation-room.sections.feedback')

                                <section class="survey-complete" x-cloak x-show="isComplete" aria-labelledby="survey-complete-title">
                                    <div class="survey-complete-mark" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="m7 12 3 3 7-7" />
                                        </svg>
                                    </div>
                                    <p>Survey complete</p>
                                    <h2 id="survey-complete-title" data-completion-title tabindex="-1">Thank you for sharing your experience.</h2>
                                    <span>Your feedback will help Boston English Center create a more useful and welcoming Conversation Room.</span>
                                    <button type="button" class="survey-button survey-button--secondary" @click="restartSurvey">
                                        Review my answers
                                    </button>
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
                                        :disabled="currentStep === 0"
                                        @click="previousQuestion"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m15 18-6-6 6-6" />
                                        </svg>
                                        Back
                                    </button>

                                    <button type="submit" class="survey-button survey-button--primary">
                                        <span x-text="nextButtonLabel"></span>
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>
                                    </button>
                                </div>
                            </footer>
                        </form>
                    </div>

                    <p class="survey-footer-note">Boston English Center <span aria-hidden="true">/</span> Student experience</p>
                </section>
            </div>
        </main>
    </body>
</html>
