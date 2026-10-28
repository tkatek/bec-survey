<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Thank you for helping Boston English Center improve the Conversation Room.">

        <title>Survey Complete | Boston English Center</title>

        <link rel="preload" as="image" href="{{ asset('images/conversation-room/conversation-room-background.webp') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="survey-page">
        <svg class="survey-icon-sprite" aria-hidden="true">
            <symbol id="survey-icon-microphone" viewBox="0 0 24 24"><rect x="9" y="2" width="6" height="12" rx="3"></rect><path d="M5 10a7 7 0 0 0 14 0M12 17v5M8 22h8"></path></symbol>
            <symbol id="survey-icon-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></symbol>
            <symbol id="survey-icon-heart" viewBox="0 0 24 24"><path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 22l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"></path></symbol>
            <symbol id="survey-icon-lock" viewBox="0 0 24 24"><path d="M7 10V8a5 5 0 0 1 10 0v2m-9 0h8a2 2 0 0 1 2 2v7H6v-7a2 2 0 0 1 2-2Z"></path></symbol>
            <symbol id="survey-icon-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></symbol>
        </svg>

        <main class="survey-layout" x-data="surveyThankYou">
            <aside class="survey-intro" data-survey-intro>
                <div class="survey-intro-brand" data-intro-reveal>
                    <img src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}" alt="Boston English Center">
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

            <section class="survey-workspace" aria-label="Conversation Room survey completion">
                <div class="survey-card survey-card--complete" data-survey-card>
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

                    <section
                        class="survey-complete survey-complete--standalone"
                        data-standalone-complete
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
                        <h2 id="survey-complete-title" data-success-content>Thank you for sharing your experience.</h2>
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
                            <a href="{{ route('conversation-room.create') }}" class="survey-button survey-button--secondary">
                                Review my answers
                            </a>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </body>
</html>
