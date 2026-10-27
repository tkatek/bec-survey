import Alpine from 'alpinejs';
import { gsap } from 'gsap';

const emptyAnswers = () => ({
    attendanceFrequency: '',
    attendanceChange: '',
    attendanceReasons: [],
    updatedRoomUse: '',
    easeOfUse: '',
    activityUsefulness: '',
    speakingComfort: '',
    sessionFormat: '',
    encouragementChanges: [],
    preferredSessionTime: '',
    timeZone: '',
    preferredDays: [],
    sessionLength: '',
    attendanceLikelihood: '',
    oneChange: '',
});

const animateSurveyComplete = (container, prefersReducedMotion) => {
    if (!container) {
        return;
    }

    const visual = container.querySelector('[data-success-visual]');
    const ring = container.querySelector('[data-success-ring]');
    const check = container.querySelector('[data-success-check]');
    const content = container.querySelectorAll('[data-success-content]');

    gsap.killTweensOf([visual, ring, check, ...content]);

    if (prefersReducedMotion) {
        gsap.set([visual, ...content], { clearProps: 'all', opacity: 1 });
        gsap.set(check, { strokeDashoffset: 0 });
        gsap.set(ring, { opacity: 0 });

        return;
    }

    gsap.set(check, { strokeDashoffset: 24 });

    gsap.timeline({ defaults: { ease: 'power3.out' } })
        .fromTo(visual, {
            opacity: 0,
            scale: 0.68,
            y: 10,
        }, {
            opacity: 1,
            scale: 1,
            y: 0,
            duration: 0.48,
            ease: 'back.out(1.65)',
        })
        .fromTo(ring, {
            opacity: 0.55,
            scale: 0.58,
        }, {
            opacity: 0,
            scale: 1.32,
            duration: 0.72,
            ease: 'power2.out',
        }, 0.05)
        .to(check, {
            strokeDashoffset: 0,
            duration: 0.38,
            ease: 'power2.out',
        }, 0.18)
        .fromTo(content, {
            opacity: 0,
            y: 12,
        }, {
            opacity: 1,
            y: 0,
            duration: 0.35,
            stagger: 0.055,
        }, 0.27)
        .to(visual, {
            y: -3,
            duration: 0.16,
            repeat: 1,
            yoyo: true,
            ease: 'sine.inOut',
        }, 0.55);
};

Alpine.data('conversationRoomSurvey', () => ({
    answers: emptyAnswers(),
    currentStep: 0,
    errorMessage: '',
    isAnimating: false,
    isComplete: false,
    isCompactViewport: false,
    optionPages: {
        2: 0,
        8: 0,
        10: 0,
    },
    optionPageSizes: {
        2: 6,
        8: 5,
        10: 7,
    },
    optionTotals: {
        2: 12,
        8: 10,
        10: 7,
    },
    prefersReducedMotion: false,
    totalSteps: 14,

    init() {
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const compactViewport = window.matchMedia('(max-width: 640px)');

        this.isCompactViewport = compactViewport.matches;
        compactViewport.addEventListener('change', (event) => {
            this.isCompactViewport = event.matches;
        });

        if (this.prefersReducedMotion) {
            return;
        }

        this.$nextTick(() => {
            gsap.timeline({ defaults: { ease: 'power3.out' } })
                .from('[data-survey-intro] [data-intro-reveal]', {
                    opacity: 0,
                    y: 20,
                    duration: 0.55,
                    stagger: 0.07,
                })
                .from('[data-survey-card]', {
                    opacity: 0,
                    x: 34,
                    duration: 0.7,
                }, 0.08);
        });
    },

    get progressPercentage() {
        if (this.isComplete) {
            return 100;
        }

        return Math.round(((this.currentStep + 1) / this.totalSteps) * 100);
    },

    get nextButtonLabel() {
        if (this.hasNextOptionPage) {
            return 'More options';
        }

        return this.currentStep === this.totalSteps - 1 ? 'Submit survey' : 'Next question';
    },

    get currentOptionPage() {
        return this.optionPages[this.currentStep] ?? 0;
    },

    get currentOptionPageCount() {
        const pageSize = this.optionPageSizes[this.currentStep];
        const optionTotal = this.optionTotals[this.currentStep];

        if (!pageSize || !optionTotal) {
            return 1;
        }

        return Math.ceil(optionTotal / pageSize);
    },

    get hasNextOptionPage() {
        return this.isCompactViewport
            && this.currentOptionPage < this.currentOptionPageCount - 1;
    },

    get hasPreviousOptionPage() {
        return this.isCompactViewport && this.currentOptionPage > 0;
    },

    clearError() {
        this.errorMessage = '';
    },

    isSectionActive(sectionIndex) {
        const ranges = [[0, 2], [3, 7], [8, 11], [12, 13]];
        const [start, end] = ranges[sectionIndex];

        return this.currentStep >= start && this.currentStep <= end;
    },

    isSectionComplete(sectionIndex) {
        const sectionEndSteps = [2, 7, 11, 13];

        return this.isComplete || this.currentStep > sectionEndSteps[sectionIndex];
    },

    isOptionVisible(questionStep, optionIndex) {
        if (!this.isCompactViewport || !this.optionPageSizes[questionStep]) {
            return true;
        }

        const pageSize = this.optionPageSizes[questionStep];
        const page = this.optionPages[questionStep] ?? 0;
        const firstVisibleOption = page * pageSize;

        return optionIndex >= firstVisibleOption
            && optionIndex < firstVisibleOption + pageSize;
    },

    limitSelections(field, maximum, event) {
        const selections = this.answers[field];

        if (selections.length <= maximum) {
            return;
        }

        const selectionIndex = selections.indexOf(event.target.value);

        if (selectionIndex !== -1) {
            selections.splice(selectionIndex, 1);
        }

        event.target.checked = false;
        this.errorMessage = `Choose up to ${maximum} options.`;
        this.animateInvalidStep();
    },

    nextQuestion() {
        if (this.isAnimating || this.isComplete) {
            return;
        }

        if (this.hasNextOptionPage) {
            this.errorMessage = '';
            this.transitionStage(() => {
                this.optionPages[this.currentStep] += 1;
            }, 1);

            return;
        }

        if (!this.isCurrentStepComplete()) {
            this.errorMessage = this.currentStep === 9
                ? 'Choose a preferred time and a time zone to continue.'
                : 'Choose an answer before continuing.';
            this.animateInvalidStep();

            return;
        }

        this.errorMessage = '';

        if (this.currentStep === this.totalSteps - 1) {
            this.isAnimating = true;
            this.$refs.surveyForm.submit();

            return;
        }

        this.transitionStage(() => {
            this.currentStep += 1;
        }, 1);
    },

    previousQuestion() {
        if (this.isAnimating || this.isComplete) {
            return;
        }

        if (this.hasPreviousOptionPage) {
            this.errorMessage = '';
            this.transitionStage(() => {
                this.optionPages[this.currentStep] -= 1;
            }, -1);

            return;
        }

        if (this.currentStep === 0) {
            return;
        }

        this.errorMessage = '';
        this.transitionStage(() => {
            this.currentStep -= 1;
        }, -1);
    },

    restartSurvey() {
        this.answers = emptyAnswers();
        this.errorMessage = '';
        this.optionPages = {
            2: 0,
            8: 0,
            10: 0,
        };

        this.transitionStage(() => {
            this.currentStep = 0;
            this.isComplete = false;
        }, -1);
    },

    animateCompletion() {
        this.$nextTick(() => {
            animateSurveyComplete(
                this.$refs.stage.querySelector('[data-survey-complete]'),
                this.prefersReducedMotion,
            );
        });
    },

    isCurrentStepComplete() {
        const requiredAnswers = [
            'attendanceFrequency',
            'attendanceChange',
            null,
            'updatedRoomUse',
            'easeOfUse',
            'activityUsefulness',
            'speakingComfort',
            'sessionFormat',
            'encouragementChanges',
            ['preferredSessionTime', 'timeZone'],
            'preferredDays',
            'sessionLength',
            'attendanceLikelihood',
            null,
        ];
        const requirement = requiredAnswers[this.currentStep];

        if (requirement === null) {
            return true;
        }

        if (Array.isArray(requirement)) {
            return requirement.every((field) => this.hasValue(this.answers[field]));
        }

        return this.hasValue(this.answers[requirement]);
    },

    hasValue(value) {
        if (Array.isArray(value)) {
            return value.length > 0;
        }

        return String(value).trim().length > 0;
    },

    transitionStage(updateState, direction) {
        const stage = this.$refs.stage;
        const getActiveContent = () => this.isComplete
            ? stage.querySelector('[data-survey-complete]')
            : stage.querySelector(`[data-survey-step="${this.currentStep}"]`);

        if (this.prefersReducedMotion) {
            updateState();
            this.focusCurrentContent();

            if (this.isComplete) {
                this.animateCompletion();
            }

            return;
        }

        this.isAnimating = true;
        const outgoingContent = getActiveContent();

        gsap.killTweensOf(stage);
        gsap.killTweensOf(outgoingContent);
        gsap.set(stage, { clearProps: 'transform' });

        gsap.to(outgoingContent, {
            opacity: 0,
            y: direction * -8,
            duration: 0.12,
            ease: 'power1.out',
            overwrite: true,
            onComplete: () => {
                gsap.set(outgoingContent, { clearProps: 'opacity,transform,visibility' });
                updateState();

                this.$nextTick(() => {
                    const incomingContent = getActiveContent();
                    const finishTransition = () => {
                        this.isAnimating = false;
                        this.focusCurrentContent();

                        if (this.isComplete) {
                            this.animateCompletion();
                        }
                    };
                    const questionHeading = incomingContent.querySelector('.survey-question-heading');
                    const optionCards = Array.from(incomingContent.querySelectorAll('.survey-option'))
                        .filter((option) => getComputedStyle(option).display !== 'none');

                    gsap.killTweensOf([incomingContent, questionHeading, ...optionCards].filter(Boolean));

                    if (optionCards.length === 0) {
                        gsap.fromTo(incomingContent, {
                            opacity: 0,
                            y: direction * 8,
                        }, {
                            opacity: 1,
                            y: 0,
                            duration: 0.18,
                            ease: 'power2.out',
                            overwrite: true,
                            clearProps: 'opacity,transform,visibility',
                            onComplete: finishTransition,
                        });

                        return;
                    }

                    const optionStagger = Math.min(0.035, 0.16 / Math.max(optionCards.length - 1, 1));
                    const timeline = gsap.timeline({ onComplete: finishTransition });

                    gsap.set(incomingContent, { clearProps: 'opacity,transform,visibility' });

                    if (questionHeading) {
                        timeline.fromTo(questionHeading, {
                            opacity: 0,
                            y: direction * 8,
                        }, {
                            opacity: 1,
                            y: 0,
                            duration: 0.18,
                            ease: 'power2.out',
                            clearProps: 'opacity,transform,visibility',
                        });
                    }

                    timeline.fromTo(optionCards, {
                        opacity: 0,
                        y: direction * 12,
                        scale: 0.985,
                    }, {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: 0.24,
                        stagger: optionStagger,
                        ease: 'power2.out',
                        clearProps: 'opacity,transform,visibility',
                    }, questionHeading ? 0.04 : 0);
                });
            },
        });
    },

    animateInvalidStep() {
        if (this.prefersReducedMotion) {
            return;
        }

        gsap.fromTo(this.$refs.stage, { x: -5 }, {
            x: 5,
            duration: 0.07,
            repeat: 3,
            yoyo: true,
            ease: 'power1.inOut',
            clearProps: 'x',
        });
    },

    focusCurrentContent() {
        this.$nextTick(() => {
            const selector = this.isComplete
                ? '[data-completion-title]'
                : `[data-survey-step="${this.currentStep}"] [data-question-title]`;

            document.querySelector(selector)?.focus({ preventScroll: true });
        });
    },
}));

Alpine.data('surveyThankYou', () => ({
    init() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        this.$nextTick(() => {
            if (prefersReducedMotion) {
                animateSurveyComplete(document.querySelector('[data-standalone-complete]'), true);

                return;
            }

            gsap.timeline({
                defaults: { ease: 'power3.out' },
                onComplete: () => {
                    animateSurveyComplete(document.querySelector('[data-standalone-complete]'), false);
                },
            })
                .from('[data-survey-intro] [data-intro-reveal]', {
                    opacity: 0,
                    y: 16,
                    duration: 0.42,
                    stagger: 0.055,
                })
                .from('[data-survey-card]', {
                    opacity: 0,
                    x: 24,
                    duration: 0.5,
                }, 0.06);
        });
    },
}));

window.Alpine = Alpine;

Alpine.start();
