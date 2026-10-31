<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Thank you for helping Boston English Center improve the Conversation Room.">

        <title>Survey Complete | Boston English Center</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bec-thanks-page">
        <div class="bec-thanks-backdrop" aria-hidden="true">
            <span class="bec-thanks-blob bec-thanks-blob--blue"></span>
            <span class="bec-thanks-blob bec-thanks-blob--mint"></span>
        </div>

        <p class="sr-only" role="status">Your survey response was submitted successfully.</p>

        <div class="bec-thanks-shell" x-data="surveyThankYou">
            <header class="bec-thanks-header" data-thanks-header>
                <a href="{{ route('conversation-room.create') }}" class="bec-thanks-brand" aria-label="Boston English Center home">
                    <img
                        src="{{ asset('images/conversation-room/boston-english-center-logo.png') }}"
                        alt="Boston English Center"
                        width="720"
                        height="240"
                    >
                </a>

                <span class="bec-thanks-status">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg>
                    Completed
                </span>
            </header>

            <main class="bec-thanks-main">
                <section class="bec-thanks-card" data-thanks-card aria-labelledby="bec-thanks-title">
                    <div class="bec-thanks-success" data-thanks-visual aria-hidden="true">
                        <span class="bec-thanks-success-glow"></span>
                        <span class="bec-thanks-success-ring" data-thanks-ring></span>
                        <span class="bec-thanks-success-ring bec-thanks-success-ring--late" data-thanks-ring-late></span>
                        <div class="bec-thanks-success-mark">
                            <svg viewBox="0 0 24 24">
                                <path class="bec-thanks-check" data-thanks-check d="m5 12 4 4L19 6"></path>
                            </svg>
                        </div>
                        <div class="bec-thanks-confetti" data-thanks-confetti></div>
                    </div>

                    <p class="bec-thanks-label" data-thanks-reveal>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg>
                        Survey completed
                    </p>

                    <h1 class="bec-thanks-title" id="bec-thanks-title" data-thanks-reveal>
                        Thank you for sharing your experience!
                    </h1>

                    <p class="bec-thanks-message" data-thanks-reveal>
                        Your feedback helps us make the Boston English Center Conversation Room more useful, welcoming, and enjoyable for everyone.
                    </p>

                    <div class="bec-thanks-divider" data-thanks-reveal aria-hidden="true">
                        <span></span>
                        <svg viewBox="0 0 24 24"><path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 22l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"></path></svg>
                        <span></span>
                    </div>

                    <div class="bec-thanks-actions" data-thanks-reveal>
                        <a href="{{ route('conversation-room.create') }}" class="bec-thanks-button">
                            Back to home
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"></path></svg>
                        </a>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
