import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import { gsap } from 'gsap';
import { surveyQuestionManager } from './survey-question-manager';
import { buildVisibleSectionSteps, getSectionTrackDensity } from './survey-stepper';

const createAnswers = (questions = []) => Object.fromEntries(
    questions.map((question) => {
        if (question.type === 'multiple_choice') {
            return [String(question.id), []];
        }

        if (question.type === 'grouped_choice') {
            return [
                String(question.id),
                Object.fromEntries(question.groups.map((group) => [group, ''])),
            ];
        }

        return [String(question.id), ''];
    }),
);

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

Alpine.data('conversationRoomSurvey', (configuration = {}) => ({
    answers: createAnswers(configuration.questions),
    currentStep: 0,
    errorMessage: '',
    isAnimating: false,
    isComplete: false,
    isPreview: configuration.isPreview ?? false,
    prefersReducedMotion: false,
    questions: configuration.questions ?? [],
    resizeFrame: null,
    resizeHandler: null,
    sections: configuration.sections ?? [],
    totalSteps: configuration.totalSteps ?? 0,
    viewportWidth: 1280,

    init() {
        this.viewportWidth = window.innerWidth;
        this.resizeHandler = () => {
            window.cancelAnimationFrame(this.resizeFrame);
            this.resizeFrame = window.requestAnimationFrame(() => {
                this.viewportWidth = window.innerWidth;
            });
        };
        window.addEventListener('resize', this.resizeHandler, { passive: true });
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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

    destroy() {
        window.removeEventListener('resize', this.resizeHandler);
        window.cancelAnimationFrame(this.resizeFrame);
    },

    get progressPercentage() {
        if (this.isComplete) {
            return 100;
        }

        return Math.round(((this.currentStep + 1) / this.totalSteps) * 100);
    },

    get nextButtonLabel() {
        if (this.currentStep !== this.totalSteps - 1) {
            return 'Next question';
        }

        return this.isPreview ? 'Finish preview' : 'Submit survey';
    },

    get sectionTrackDensity() {
        return getSectionTrackDensity(this.sections.length, this.viewportWidth);
    },

    get visibleSectionSteps() {
        return buildVisibleSectionSteps(
            this.sections,
            this.currentStep,
            this.viewportWidth,
            this.isComplete,
        );
    },

    clearError() {
        this.errorMessage = '';
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

        if (!this.isCurrentStepComplete()) {
            this.errorMessage = this.questions[this.currentStep]?.type === 'grouped_choice'
                ? 'Choose one answer from every group before continuing.'
                : 'Choose an answer before continuing.';
            this.animateInvalidStep();

            return;
        }

        this.errorMessage = '';

        if (this.currentStep === this.totalSteps - 1) {
            if (this.isPreview) {
                this.transitionStage(() => {
                    this.isComplete = true;
                }, 1);

                return;
            }

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

        if (this.currentStep === 0) {
            return;
        }

        this.errorMessage = '';
        this.transitionStage(() => {
            this.currentStep -= 1;
        }, -1);
    },

    restartSurvey() {
        this.answers = createAnswers(this.questions);
        this.errorMessage = '';

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
        const question = this.questions[this.currentStep];

        if (!question || !question.required) {
            return true;
        }

        const answer = this.answers[String(question.id)];

        if (question.type === 'grouped_choice') {
            return question.groups.every((group) => this.hasValue(answer[group]));
        }

        return this.hasValue(answer);
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

Alpine.data('dashboardAnalytics', (config) => ({
    analytics: config.analytics,
    snapshotToken: config.snapshotToken,
    loading: false,
    exporting: false,
    errorMessage: '',
    calendarOpen: false,
    calendarMode: 'range',
    calendarError: '',
    rowTooltip: null,
    abortController: null,
    charts: {},
    animated: { responses: 0, positive: 0 },
    colors: ['#1457ff', '#2bcf9b', '#ffbb3d', '#9a63ed', '#ff5f78', '#1bb7d8'],
    softColors: ['#e8f0ff', '#e7faf4', '#fff5df', '#f4ebff', '#ffecef', '#e7f8fc'],
    presets: [
        { value: 'today', label: 'Today' },
        { value: 'last7', label: 'Last 7 days' },
        { value: 'month', label: 'This month' },
    ],
    filter: {
        preset: config.analytics.meta.filter.preset,
        start: config.analytics.meta.filter.start,
        end: config.analytics.meta.filter.end,
        version: config.analytics.meta.version,
    },
    draftStart: config.analytics.meta.filter.start,
    draftEnd: config.analytics.meta.filter.end,
    today: new Date().toLocaleDateString('en-CA'),

    init() {
        this.animateCounters();
        this.$nextTick(() => this.renderCharts());
    },

    async refresh() {
        this.abortController?.abort();
        this.abortController = new AbortController();
        this.loading = true;
        this.errorMessage = '';

        const query = new URLSearchParams({
            preset: this.filter.preset,
            version: String(this.filter.version),
        });

        if (this.filter.preset === 'custom') {
            query.set('start', this.filter.start);
            query.set('end', this.filter.end);
        }

        try {
            const response = await fetch(`${config.dataUrl}?${query.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                throw new Error(payload.message ?? 'The analytics could not be refreshed.');
            }

            const payload = await response.json();
            this.analytics = payload.analytics;
            this.snapshotToken = payload.snapshotToken;
            this.filter.start = payload.analytics.meta.filter.start;
            this.filter.end = payload.analytics.meta.filter.end;
            this.draftStart = this.filter.start;
            this.draftEnd = this.filter.end;
            window.history.replaceState({}, '', `${config.dashboardUrl}?${new URLSearchParams(payload.analytics.meta.query).toString()}`);
            this.animateCounters();
            await this.$nextTick();
            this.renderCharts();
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.errorMessage = error.message;
            }
        } finally {
            if (!this.abortController?.signal.aborted) {
                this.loading = false;
            }
        }
    },

    applyPreset(preset) {
        this.filter.preset = preset;
        this.calendarOpen = false;
        this.refresh();
    },

    applyCustomDates() {
        this.calendarError = '';

        if (!this.draftStart || !this.draftEnd) {
            this.calendarError = 'Choose both a start date and an end date.';
            return;
        }

        if (this.draftStart > this.draftEnd) {
            this.calendarError = 'The end date must be on or after the start date.';
            return;
        }

        this.filter.preset = 'custom';
        this.filter.start = this.draftStart;
        this.filter.end = this.calendarMode === 'date' ? this.draftStart : this.draftEnd;
        this.calendarOpen = false;
        this.refresh();
    },

    setTodayDraft() {
        this.draftStart = this.today;
        this.draftEnd = this.today;
        this.calendarMode = 'date';
    },

    closeCalendar() {
        this.calendarOpen = false;
        this.calendarError = '';
        this.$nextTick(() => this.$refs.calendarTrigger?.focus());
    },

    animateCounters() {
        const targets = {
            responses: Number(this.analytics.overview.responses ?? 0),
            positive: Number(this.analytics.overview.positiveActivity.percentage ?? 0),
        };

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.animated = targets;
            return;
        }

        gsap.killTweensOf(this.animated);
        this.animated.responses = 0;
        this.animated.positive = 0;
        gsap.to(this.animated, {
            ...targets,
            duration: 0.85,
            ease: 'power2.out',
        });
    },

    renderCharts() {
        Object.values(this.charts).forEach((chart) => {
            chart?.stop();
            chart?.destroy();
        });
        this.charts = {};
        this.charts.responses = this.renderResponsesChart();
        this.charts.attendance = this.renderDoughnutChart();
        this.charts.barriers = this.renderBarChart('barriersChart', this.analytics.dashboard.barriers, true);
        this.charts.preferredTime = this.renderBarChart('preferredTimeChart', this.analytics.dashboard.preferredTime);
        this.charts.likelihood = this.renderBarChart('likelihoodChart', this.analytics.dashboard.likelihood);
    },

    renderResponsesChart() {
        if (!this.$refs.responsesChart) {
            return null;
        }

        const context = this.$refs.responsesChart.getContext('2d');
        const gradient = context.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(20, 87, 255, .28)');
        gradient.addColorStop(1, 'rgba(20, 87, 255, 0)');

        return new Chart(context, {
            type: 'line',
            data: {
                labels: this.analytics.series.points.map((point) => point.label),
                datasets: [{
                    data: this.analytics.series.points.map((point) => point.value),
                    borderColor: '#1457ff',
                    backgroundColor: gradient,
                    fill: true,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#1457ff',
                    pointBorderWidth: 2,
                    pointRadius: this.analytics.series.points.length > 35 ? 0 : 3,
                    pointHoverRadius: 6,
                    tension: 0.36,
                }],
            },
            options: this.chartOptions({
                interaction: { mode: 'index', intersect: false, axis: 'x' },
                tooltip: (contextItem) => {
                    const point = this.analytics.series.points[contextItem.dataIndex];
                    const responseLabel = point.value === 1 ? 'response' : 'responses';
                    const dateLines = String(point.fullLabel)
                        .split(' – ')
                        .flatMap((part) => this.wrapTooltipText(part, 30));

                    return [
                        `${point.value} ${responseLabel}`,
                        ...dateLines,
                        this.analytics.series.timezone,
                    ];
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 9, color: '#71809d' } },
                    y: { beginAtZero: true, grid: { color: '#edf1f7' }, ticks: { precision: 0, color: '#71809d' } },
                },
            }),
        });
    },

    renderDoughnutChart() {
        const distribution = this.distribution(this.analytics.dashboard.attendance);

        if (!this.$refs.attendanceChart) {
            return null;
        }

        const centerLabel = {
            id: 'attendanceCenterLabel',
            beforeDraw: (chart) => {
                const { ctx, chartArea } = chart;

                if (!chartArea) {
                    return;
                }

                ctx.save();
                ctx.textAlign = 'center';
                ctx.fillStyle = '#0b1739';
                ctx.font = '800 24px Plus Jakarta Sans';
                ctx.fillText(String(distribution.answered), (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2 - 2);
                ctx.fillStyle = '#71809d';
                ctx.font = '600 11px Plus Jakarta Sans';
                ctx.fillText(distribution.answered === 1 ? 'answering Q1' : 'answering Q1', (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2 + 18);
                ctx.restore();
            },
        };

        return new Chart(this.$refs.attendanceChart, {
            type: 'doughnut',
            data: {
                labels: distribution.options.map((item) => item.label),
                datasets: [{
                    data: distribution.options.map((item) => item.count),
                    backgroundColor: this.colors,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }],
            },
            options: this.chartOptions({
                cutout: '66%',
                tooltip: (contextItem) => this.tooltipLabel(contextItem, distribution.answered),
            }),
            plugins: [centerLabel],
        });
    },

    renderBarChart(ref, question, horizontal = false) {
        const canvas = this.$refs[ref];
        const distribution = this.distribution(question);

        if (!canvas) {
            return null;
        }

        const options = horizontal
            ? distribution.options
                .map((item, index) => ({ ...item, originalOrder: index }))
                .sort((left, right) => (right.count - left.count) || (left.originalOrder - right.originalOrder))
                .slice(0, 6)
            : distribution.options;

        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: options.map((item) => item.label),
                datasets: [{
                    data: options.map((item) => item.count),
                    backgroundColor: options.map((item, index) => this.colors[index % this.colors.length]),
                    borderRadius: 7,
                    borderSkipped: false,
                    maxBarThickness: horizontal ? 24 : 48,
                }],
            },
            options: this.chartOptions({
                indexAxis: horizontal ? 'y' : 'x',
                tooltip: (contextItem) => this.tooltipLabel(contextItem, distribution.answered),
                scales: horizontal
                    ? {
                        x: { beginAtZero: true, grid: { color: '#edf1f7' }, ticks: { precision: 0, color: '#71809d' } },
                        y: { grid: { display: false }, ticks: { color: '#52658b', autoSkip: false } },
                    }
                    : {
                        x: { grid: { display: false }, ticks: { color: '#71809d', maxRotation: 28, minRotation: 0 } },
                        y: { beginAtZero: true, grid: { color: '#edf1f7' }, ticks: { precision: 0, color: '#71809d' } },
                    },
            }),
        });
    },

    chartOptions({ tooltip, ...options }) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 700 },
            interaction: { mode: 'nearest', intersect: true },
            plugins: {
                legend: { display: false },
                tooltip: {
                    displayColors: true,
                    backgroundColor: '#0b1739',
                    padding: 12,
                    titleFont: { family: 'Plus Jakarta Sans', size: 14, weight: '700' },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 14 },
                    callbacks: {
                        title: (items) => this.wrapTooltipText(items[0]?.label ?? '', 30),
                        label: tooltip,
                    },
                },
            },
            ...options,
        };
    },

    tooltipLabel(contextItem, denominator) {
        const count = Number(contextItem.raw ?? 0);
        const percentage = denominator > 0 ? ((count / denominator) * 100).toFixed(1) : '0.0';
        const responseLabel = count === 1 ? 'response' : 'responses';

        return [
            `${count} ${responseLabel} · ${percentage}%`,
            `Of ${denominator} answering this question`,
        ];
    },

    wrapTooltipText(value, maxCharacters = 30) {
        const words = String(value).split(/\s+/).filter(Boolean);
        const lines = [];
        let line = '';

        words.forEach((word) => {
            const candidate = line ? `${line} ${word}` : word;

            if (candidate.length > maxCharacters && line) {
                lines.push(line);
                line = word;
            } else {
                line = candidate;
            }
        });

        if (line) {
            lines.push(line);
        }

        return lines;
    },

    distribution(question, group = null) {
        const distributions = question?.distributions ?? [];
        return distributions.find((item) => item.group === group)
            ?? distributions[0]
            ?? { answered: 0, options: [] };
    },

    rankedOptions(question) {
        return [...this.distribution(question).options].sort((left, right) => right.count - left.count);
    },

    formatNumber(value) {
        return Math.round(Number(value ?? 0)).toLocaleString();
    },

    formatPercent(value) {
        return value === null || value === undefined ? 'N/A' : `${Number(value).toFixed(Number(value) % 1 ? 1 : 0)}%`;
    },

    detailUrl(question) {
        const query = new URLSearchParams({ ...this.analytics.meta.query, question });
        return `${config.commonAnswersUrl}?${query.toString()}`;
    },

    feedbackUrl() {
        const query = new URLSearchParams({ ...this.analytics.meta.query, tab: 'feedback' });
        return `${config.commonAnswersUrl}?${query.toString()}`;
    },

    showRowTooltip(item, denominator) {
        this.rowTooltip = { ...item, denominator };
    },
}));

Alpine.data('questionEditor', (initialOptions = [], initialType = 'single_choice') => ({
    type: initialType,
    options: initialOptions.length > 0
        ? initialOptions.map((option, index) => ({ ...option, clientKey: `existing-${option.id ?? index}` }))
        : [
            { id: null, label: '', icon: 'message-circle', tone: 'blue', group_name: '', clientKey: 'new-1' },
            { id: null, label: '', icon: 'message-circle', tone: 'green', group_name: '', clientKey: 'new-2' },
        ],
    nextOptionKey: initialOptions.length + 1,

    addOption() {
        this.nextOptionKey += 1;
        const tones = ['blue', 'green', 'orange', 'purple', 'red'];

        this.options.push({
            id: null,
            label: '',
            icon: 'message-circle',
            tone: tones[(this.options.length) % tones.length],
            group_name: '',
            clientKey: `new-${this.nextOptionKey}`,
        });
    },

    removeOption(index) {
        if (this.options.length <= 2) {
            return;
        }

        this.options.splice(index, 1);
    },
}));

Alpine.data('surveyQuestionManager', surveyQuestionManager);

window.Alpine = Alpine;

Alpine.start();
