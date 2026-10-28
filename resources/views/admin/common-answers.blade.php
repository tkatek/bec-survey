@php
    $filterQuery = $analytics['meta']['query'];
    $contextQuery = array_filter([
        'tab' => $selectedTab,
        'section' => $selectedTab === 'selected' ? $selectedSection : null,
        'question' => $selectedTab === 'selected' ? $selectedQuestion : null,
        'search' => $selectedTab === 'feedback' ? $search : null,
        'theme' => $selectedTab === 'feedback' ? $selectedTheme : null,
        'alert' => $selectedAlert['id'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $pdfQuery = array_merge($filterQuery, ['snapshot' => $snapshotToken]);
    $questionOptions = collect($analytics['questions'])
        ->reject(fn (array $question): bool => $question['type'] === \App\Models\SurveyQuestion::TYPE_LONG_TEXT)
        ->map(fn (array $question): array => [
            'key' => $question['key'],
            'section' => $question['section'],
            'label' => 'Q'.$question['questionOrder'].' · '.Str::limit($question['prompt'], 58),
        ])
        ->values();
    $writtenFeedbackCount = (int) $analytics['overview']['writtenFeedback']['count'];
@endphp

<x-app-layout>
    <div
        x-data="dashboardAnalytics({{
            Js::from([
                'analytics' => $analytics,
                'snapshotToken' => $snapshotToken,
                'dataUrl' => route('admin.analytics.show'),
                'dashboardUrl' => route('dashboard'),
                'historyUrl' => route('admin.common-answers'),
                'commonAnswersUrl' => route('admin.common-answers'),
                'contextQuery' => $contextQuery,
                'navigateOnFilter' => true,
                'selectedQuestion' => $selectedQuestion,
            ])
        }})"
        class="admin-analytics-page admin-secondary-page relative min-h-screen overflow-x-hidden"
    >
        <div x-cloak x-show="loading" class="fixed inset-0 z-[60] grid place-items-center bg-[#f5f8ff]/70 backdrop-blur-sm" aria-live="polite">
            <div class="flex items-center gap-3 rounded-2xl border border-[#dce5f2] bg-white px-5 py-4 text-sm font-bold text-[#334a75] shadow-xl">
                <span class="h-5 w-5 animate-spin rounded-full border-2 border-[#b8c9e7] border-t-[#1457ff]"></span>
                Updating analytics…
            </div>
        </div>

        <div class="mx-auto w-full max-w-[1600px] px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
            <header class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-end">
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#1457ff]">Survey analytics</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.035em] text-[#0b1739] sm:text-[2rem]">Common Answers</h1>
                    <p class="mt-2 max-w-4xl text-sm leading-6 text-[#64748b]">See the most selected answers, recurring feedback themes, and key insights from your Conversation Room survey.</p>
                </div>

                <div class="flex min-w-0 flex-col gap-2 xl:items-end">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="relative" @keydown.escape.stop="closeCalendar()">
                            <button x-ref="calendarTrigger" type="button" @click="calendarOpen = !calendarOpen" :aria-expanded="calendarOpen" aria-haspopup="dialog" class="admin-control inline-flex h-11 items-center gap-2 rounded-xl px-4 text-xs font-extrabold text-[#334a75] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]">
                                <svg class="h-4 w-4 text-[#1457ff]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" /></svg>
                                <span x-text="analytics.meta.filter.label"></span>
                                <svg class="h-3.5 w-3.5 text-[#71809d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                            </button>

                            <div x-cloak x-show="calendarOpen" x-transition.origin.top.right @click.outside="closeCalendar()" role="dialog" aria-modal="true" aria-label="Choose reporting dates" class="admin-popover fixed inset-x-3 top-20 z-50 rounded-2xl border p-4 sm:absolute sm:inset-x-auto sm:right-0 sm:top-14 sm:w-[23rem]">
                                <div class="flex items-center justify-between gap-3">
                                    <div><h2 class="text-sm font-extrabold text-[#0b1739]">Choose reporting dates</h2><p class="mt-1 text-xs text-[#64748b]">Dates use <span x-text="analytics.meta.timezone"></span>.</p></div>
                                    <button type="button" @click="closeCalendar()" class="rounded-lg p-2 text-[#64748b] hover:bg-[#f1f5ff]" aria-label="Close date picker"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18" /></svg></button>
                                </div>
                                <div class="mt-4 inline-flex rounded-lg bg-[#edf3ff] p-1">
                                    <button type="button" @click="calendarMode = 'date'; draftEnd = draftStart" :class="calendarMode === 'date' ? 'bg-white text-[#1457ff] shadow-sm' : 'text-[#52658b]'" class="rounded-md px-3 py-1.5 text-xs font-bold">Specific date</button>
                                    <button type="button" @click="calendarMode = 'range'" :class="calendarMode === 'range' ? 'bg-white text-[#1457ff] shadow-sm' : 'text-[#52658b]'" class="rounded-md px-3 py-1.5 text-xs font-bold">Date range</button>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Start date<input x-model="draftStart" @change="if (calendarMode === 'date') draftEnd = draftStart" type="date" :max="today" class="w-full rounded-xl border-[#cbd5e1] text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"></label>
                                    <label x-show="calendarMode === 'range'" class="grid gap-1.5 text-xs font-bold text-[#334155]">End date<input x-model="draftEnd" type="date" :min="draftStart" :max="today" class="w-full rounded-xl border-[#cbd5e1] text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"></label>
                                </div>
                                <p x-show="calendarError" x-text="calendarError" class="mt-3 text-xs font-bold text-rose-600" role="alert"></p>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <button type="button" @click="setTodayDraft()" class="text-xs font-bold text-[#1457ff] hover:underline">Today</button>
                                    <div class="flex gap-2"><button type="button" @click="closeCalendar()" class="rounded-lg border border-[#dce5f2] px-3 py-2 text-xs font-bold text-[#52658b]">Cancel</button><button type="button" @click="applyCustomDates()" class="rounded-lg bg-[#1457ff] px-4 py-2 text-xs font-bold text-white hover:bg-[#0f46cf]">Apply</button></div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.reports.pdf') }}" @submit="exporting = true">
                            @csrf
                            @foreach ($pdfQuery as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach
                            <button type="submit" :disabled="exporting" class="admin-primary-action inline-flex h-11 items-center gap-2 rounded-xl px-4 text-xs font-extrabold text-white disabled:cursor-wait disabled:opacity-60">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5" /><path d="M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" /></svg>
                                <span x-text="exporting ? 'Preparing…' : 'Download PDF'"></span>
                            </button>
                        </form>
                        <x-admin.notification-bell :alert-data="$alertData" />
                    </div>
                    <p class="text-xs font-semibold text-[#64748b]">{{ $analytics['meta']['scope'] }} · {{ $analytics['meta']['timezone'] }} · {{ number_format($analytics['overview']['responses']) }} responses</p>
                </div>
            </header>

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            @if ($selectedAlert)
                <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5" aria-labelledby="alert-evidence-title">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div><p class="text-xs font-extrabold uppercase tracking-[0.1em] text-amber-700">Alert evidence</p><h2 id="alert-evidence-title" class="mt-1 text-lg font-extrabold text-[#0b1739]">{{ $selectedAlert['title'] }}</h2><p class="mt-2 max-w-4xl text-sm leading-6 text-[#52658b]">{{ $selectedAlert['explanation'] }}</p></div>
                        <span class="w-fit rounded-full bg-white px-3 py-1.5 text-xs font-extrabold text-amber-700 shadow-sm">{{ count($selectedAlert['evidenceResponseIds']) }} supporting responses</span>
                    </div>
                </section>
            @endif

            <nav class="admin-answer-tabs mt-6 flex w-fit max-w-full gap-1 overflow-x-auto rounded-2xl p-1" aria-label="Answer views">
                <a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'selected'])) }}" @class(['admin-answer-tab', 'is-active' => $selectedTab === 'selected'])>Selected Answers</a>
                <a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'feedback'])) }}" @class(['admin-answer-tab', 'is-active' => $selectedTab === 'feedback'])>Written Feedback Themes</a>
            </nav>

            <form
                method="GET"
                action="{{ route('admin.common-answers') }}"
                x-data="{ section: {{ Js::from($selectedSection) }}, question: {{ Js::from($selectedQuestion) }}, questions: {{ Js::from($questionOptions) }} }"
                class="admin-card mt-4 grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-4 min-[1400px]:grid-cols-[minmax(19rem,1.15fr)_minmax(10rem,.7fr)_minmax(12rem,.85fr)_auto] min-[1400px]:items-end"
            >
                <input type="hidden" name="preset" :value="filter.preset">
                <input type="hidden" name="start" :value="filter.start">
                <input type="hidden" name="end" :value="filter.end">
                <input type="hidden" name="tab" value="{{ $selectedTab }}">
                @if ($selectedAlert)<input type="hidden" name="alert" value="{{ $selectedAlert['id'] }}">@endif

                <fieldset class="min-w-0">
                    <legend class="text-xs font-bold text-[#334155]">Period</legend>
                    <div class="mt-1.5 flex flex-wrap gap-2">
                        <template x-for="preset in presets" :key="preset.value">
                            <button type="button" @click="applyPreset(preset.value)" :class="filter.preset === preset.value ? 'border-[#1457ff] bg-[#1457ff] text-white shadow-sm' : 'border-[#dce5f2] bg-white text-[#334a75] hover:border-[#b8c9e7]'" class="h-10 rounded-xl border px-3 text-xs font-extrabold transition" x-text="preset.label"></button>
                        </template>
                        <button type="button" @click="$refs.calendarTrigger.click()" :class="filter.preset === 'custom' ? 'border-[#1457ff] bg-[#edf3ff] text-[#1457ff]' : 'border-[#dce5f2] bg-white text-[#334a75] hover:border-[#b8c9e7]'" class="inline-flex h-10 items-center gap-2 rounded-xl border px-3 text-xs font-extrabold transition"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" /></svg>Custom range</button>
                    </div>
                </fieldset>

                @if ($selectedTab === 'selected')
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Section
                        <select name="section" x-model="section" @change="question = ''" class="h-10 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="">All sections</option>@foreach ($sections as $section)<option value="{{ $section }}">{{ $section }}</option>@endforeach</select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Question
                        <select name="question" x-model="question" class="h-10 min-w-0 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="">All questions</option><template x-for="item in questions.filter((item) => !section || item.section === section)" :key="item.key"><option :value="item.key" x-text="item.label"></option></template></select>
                    </label>
                @else
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Feedback theme
                        <select name="theme" class="h-10 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="">All themes</option>@foreach ($themes as $theme)<option value="{{ $theme->key }}" @selected($selectedTheme === $theme->key)>{{ $theme->label }} ({{ (int) ($themeCounts[$theme->key] ?? 0) }})</option>@endforeach</select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Search comments
                        <input name="search" value="{{ $search }}" type="search" placeholder="Search written feedback" class="h-10 rounded-xl border-[#dce5f2] text-sm focus:border-[#1457ff] focus:ring-[#1457ff]">
                    </label>
                @endif

                <div class="flex gap-2">
                    <button type="submit" class="h-10 rounded-xl bg-[#1457ff] px-5 text-xs font-extrabold text-white shadow-sm transition hover:bg-[#0f46cf]">Apply Filters</button>
                    <a href="{{ route('admin.common-answers', ['preset' => 'month', 'tab' => $selectedTab]) }}" class="grid h-10 place-items-center rounded-xl border border-[#dce5f2] px-4 text-xs font-bold text-[#52658b] transition hover:border-[#b8c9e7]">Reset</a>
                </div>
            </form>

            @if ($selectedTab === 'selected' && $selectedQuestion !== '')
                <template x-if="questionByKey()">
                    <section id="question-breakdown" class="admin-card mt-4 scroll-mt-6 p-5 sm:p-6" aria-labelledby="question-breakdown-title">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0"><p class="text-xs font-extrabold text-[#1457ff]" x-text="questionByKey().section"></p><h2 id="question-breakdown-title" class="mt-1 text-lg font-extrabold leading-7 text-[#0b1739]" x-text="questionByKey().prompt"></h2><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-text="questionByKey().answered"></span> answered · <span x-text="questionByKey().skipped"></span> skipped<span x-show="questionByKey().isMultiSelect"> · Multiple selections allowed; percentages may exceed 100%.</span></p></div>
                            <a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'selected', 'section' => $selectedSection])) }}" class="admin-detail-link">Close detail</a>
                        </div>
                        <div class="mt-5 grid gap-5" :class="questionByKey().distributions.length > 1 ? 'xl:grid-cols-2' : ''">
                            <template x-for="distributionItem in questionByKey().distributions" :key="distributionItem.group ?? 'all'">
                                <div class="min-w-0">
                                    <h3 x-show="distributionItem.group" class="mb-3 text-sm font-extrabold text-[#334155]" x-text="distributionItem.group"></h3>
                                    <div class="grid gap-2">
                                        <template x-for="option in distributionItem.options" :key="option.id">
                                            <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 gap-y-2 rounded-xl border border-[#e5ebf5] px-4 py-3">
                                                <span class="min-w-0 text-sm font-bold leading-5 text-[#334155]" x-text="option.label"></span><span class="whitespace-nowrap text-sm font-extrabold text-[#0b1739]"><span x-text="option.count"></span> · <span x-text="formatPercent(option.percentage)"></span></span>
                                                <div class="col-span-2 h-2 overflow-hidden rounded-full bg-[#e9eef7]"><div class="h-full rounded-full bg-[#1457ff]" :style="`width:${Math.min(100, Number(option.percentage ?? 0))}%`"></div></div>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="mt-2 text-xs text-[#71809d]">Of <span x-text="distributionItem.answered"></span> responses answering this <span x-text="distributionItem.group ? 'group' : 'question'"></span>.</p>
                                </div>
                            </template>
                        </div>
                    </section>
                </template>
            @endif

            @if ($analytics['overview']['responses'] === 0)
                <section class="admin-card mt-5 overflow-hidden px-5 py-14 text-center sm:py-20">
                    <div class="mx-auto grid h-20 w-20 place-items-center rounded-[1.75rem] bg-[#edf3ff] text-[#1457ff] shadow-[inset_0_0_0_1px_rgba(20,87,255,.08)]">
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2" /><path d="m3 6 6-4 6 5 6-4" /></svg>
                    </div>
                    <h2 class="mt-6 text-xl font-extrabold tracking-[-0.025em] text-[#0b1739]">No survey responses yet</h2>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-[#64748b]">Once students submit feedback, their most common answers and insights will appear here.</p>
                    <a href="{{ route('conversation-room.create') }}" target="_blank" rel="noopener" class="mt-6 inline-flex h-11 items-center gap-2 rounded-xl bg-[#1457ff] px-5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#0f46cf] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff] focus-visible:ring-offset-2"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3h7v7M10 14 21 3" /><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5" /></svg>Open Student Survey</a>
                </section>
            @elseif ($selectedTab === 'selected')
                <section class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Common answer summary">
                    <article class="admin-card min-h-[8.25rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[#e8f0ff] text-[#1457ff]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /></svg></span><div><p class="text-sm font-semibold text-[#334a75]">Total Responses</p><p class="mt-1 text-3xl font-extrabold tracking-[-0.04em] text-[#0b1739]">{{ number_format($analytics['overview']['responses']) }}</p><p class="mt-1 text-xs leading-5 text-[#71809d]">Completed, non-demo submissions</p></div></div></article>
                    <article class="admin-card min-h-[8.25rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 12.5 10.5 15 16 9.5" /><circle cx="12" cy="12" r="9" /></svg></span><div><p class="text-sm font-semibold text-[#334a75]">Positive Activity Feedback</p><p class="mt-1 text-3xl font-extrabold tracking-[-0.04em] text-[#0b1739]">{{ $analytics['overview']['positiveActivity']['percentage'] === null ? 'N/A' : rtrim(rtrim(number_format($analytics['overview']['positiveActivity']['percentage'], 1), '0'), '.').'%' }}</p><p class="mt-1 text-xs leading-5 text-[#71809d]">{{ $analytics['overview']['positiveActivity']['count'] }} of {{ $analytics['overview']['positiveActivity']['denominator'] }} found the activities useful</p></div></div></article>
                    <article class="admin-card min-h-[8.25rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-50 text-amber-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01" /><path d="M10.3 3.8 2.5 17.3A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.7L13.7 3.8a2 2 0 0 0-3.4 0Z" /></svg></span><div class="min-w-0"><p class="text-sm font-semibold text-[#334a75]">Main Issue Identified</p><p class="mt-1 line-clamp-2 text-lg font-extrabold leading-6 text-[#0b1739]">{{ $analytics['overview']['mainBarrier']['count'] === 0 ? 'No data yet' : ($analytics['overview']['mainBarrier']['isTie'] ? count($analytics['overview']['mainBarrier']['leaders']).' barriers tied' : $analytics['overview']['mainBarrier']['label']) }}</p><p class="mt-1 text-xs leading-5 text-[#71809d]">{{ $analytics['overview']['mainBarrier']['percentage'] === null ? 'N/A' : rtrim(rtrim(number_format($analytics['overview']['mainBarrier']['percentage'], 1), '0'), '.').'%' }} of {{ $analytics['overview']['mainBarrier']['denominator'] }} answering</p></div></div></article>
                    <article class="admin-card min-h-[8.25rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-rose-50 text-rose-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" /><path d="M8 10h.01M12 10h.01M16 10h.01" /></svg></span><div><p class="text-sm font-semibold text-[#334a75]">Written Feedback</p><p class="mt-1 text-3xl font-extrabold tracking-[-0.04em] text-[#0b1739]">{{ number_format($writtenFeedbackCount) }}</p><p class="mt-1 text-xs leading-5 text-[#71809d]">Distinct responses with comments</p></div></div></article>
                </section>

                <section class="mt-4 grid min-w-0 gap-4 xl:grid-cols-12">
                    <article class="admin-card min-w-0 p-5 sm:p-6 xl:col-span-7">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="admin-card-title">Attendance Frequency</h2><p class="admin-card-subtitle">How often have students attended the Conversation Room in the past month?</p></div><span class="rounded-full bg-[#edf3ff] px-3 py-1.5 text-xs font-bold text-[#1457ff]" x-text="distribution(analytics.dashboard.attendance).answered + ' responses'"></span></div>
                        <div class="mt-4 grid min-w-0 items-center gap-5 md:grid-cols-[minmax(13rem,.85fr)_minmax(0,1.15fr)]">
                            <div class="relative mx-auto h-[15rem] w-full max-w-[16rem]"><canvas x-ref="attendanceChart" aria-label="Attendance frequency doughnut chart"></canvas></div>
                            <div class="grid min-w-0 gap-3"><template x-for="(item, index) in distribution(analytics.dashboard.attendance).options" :key="item.id"><button type="button" @click="showRowTooltip(item, distribution(analytics.dashboard.attendance).answered)" class="grid min-w-0 grid-cols-[auto_minmax(0,1fr)_auto_auto] items-center gap-2 text-left text-xs focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]"><span class="h-2.5 w-2.5 rounded-full" :style="'background:' + colors[index % colors.length]"></span><span class="min-w-0 font-semibold leading-5 text-[#334155]" x-text="item.label"></span><span class="font-extrabold text-[#0b1739]" x-text="item.count"></span><span class="w-10 text-right font-bold text-[#52658b]" x-text="formatPercent(item.percentage)"></span><span class="col-start-2 col-span-3 h-2 overflow-hidden rounded-full bg-[#e9eef7]"><span class="block h-full rounded-full" :style="`width:${Math.min(100, Number(item.percentage ?? 0))}%;background:${colors[index % colors.length]}`"></span></span></button></template></div>
                        </div>
                    </article>

                    <article class="admin-card min-w-0 p-5 sm:p-6 xl:col-span-5">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Attendance Barriers</h2><p class="admin-card-subtitle">Main reasons students attend less often or don't attend.</p></div><a :href="detailUrl('attendance_reasons')" class="admin-detail-link">View all reasons</a></div>
                        <div class="mt-5 grid gap-3"><template x-for="(item, index) in rankedOptions(analytics.dashboard.barriers).slice(0, 6)" :key="item.id"><button type="button" @click="showRowTooltip(item, distribution(analytics.dashboard.barriers).answered)" class="group grid min-w-0 grid-cols-[minmax(0,1fr)_auto_auto] items-center gap-x-3 gap-y-2 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]"><span class="min-w-0 text-xs font-semibold leading-5 text-[#334155]" x-text="item.label"></span><span class="text-xs font-extrabold text-[#0b1739]" x-text="item.count"></span><span class="w-10 text-right text-xs font-bold text-[#52658b]" x-text="formatPercent(item.percentage)"></span><span class="col-span-3 h-2 overflow-hidden rounded-full bg-[#e9eef7]"><span class="block h-full rounded-full" :style="`width:${Math.min(100, Number(item.percentage ?? 0))}%;background:${colors[index % colors.length]}`"></span></span></button></template></div>
                        <p class="mt-4 text-xs leading-5 text-[#71809d]">Percentages use the {{ $analytics['dashboard']['barriers']['answered'] ?? 0 }} respondents who answered this multiple-choice question, so totals may exceed 100%.</p>
                    </article>
                </section>

                <section class="mt-4 grid min-w-0 gap-4 lg:grid-cols-2">
                    <article class="admin-card min-w-0 self-start p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Preferred Session Format</h2><p class="admin-card-subtitle">Which activities and formats students want most.</p></div><a :href="detailUrl('session_format')" class="admin-detail-link">View details</a></div>
                        <div class="mt-5 grid grid-cols-2 gap-3 xl:grid-cols-3 min-[1400px]:grid-cols-5"><template x-for="(item, index) in distribution(analytics.dashboard.format).options" :key="item.id"><button type="button" @click="showRowTooltip(item, distribution(analytics.dashboard.format).answered)" class="flex min-h-[10rem] min-w-0 flex-col items-center rounded-2xl border border-[#e5ebf5] bg-[#fbfcff] p-3 text-center transition hover:border-[#b8c9e7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]"><span class="grid h-10 w-10 place-items-center rounded-xl" :style="`background:${softColors[index % softColors.length]};color:${colors[index % colors.length]}`"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" /><path d="M8 10h8M8 14h5" /></svg></span><strong class="mt-2 text-xl font-extrabold text-[#0b1739]" x-text="formatPercent(item.percentage)"></strong><span class="mt-1 flex-1 text-xs font-semibold leading-4 text-[#334155]" x-text="item.label"></span><span class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-[#e9eef7]"><span class="block h-full rounded-full" :style="`width:${Math.min(100, Number(item.percentage ?? 0))}%;background:${colors[index % colors.length]}`"></span></span></button></template></div>
                    </article>

                    <article class="admin-card min-w-0 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Preferred Time to Attend</h2><p class="admin-card-subtitle">When students prefer to join live speaking sessions.</p></div><a :href="detailUrl('preferred_session_time')" class="admin-detail-link">View details</a></div>
                        <div class="relative mt-4 h-[16rem] min-w-0"><canvas x-ref="preferredTimeChart" aria-label="Preferred time to attend vertical bar chart"></canvas></div>
                        <details class="mt-3 text-xs text-[#52658b]"><summary class="cursor-pointer font-bold text-[#1457ff]">Accessible data table</summary><div class="mt-2 grid gap-2"><template x-for="item in distribution(analytics.dashboard.preferredTime, 'Preferred time').options" :key="item.id"><div class="flex items-start justify-between gap-4 rounded-lg bg-[#f5f8ff] px-3 py-2"><span x-text="item.label"></span><strong><span x-text="item.count"></span> · <span x-text="formatPercent(item.percentage)"></span></strong></div></template></div></details>
                    </article>
                </section>

                <section class="mt-4 grid min-w-0 gap-4 xl:grid-cols-12">
                    <article class="admin-card min-w-0 self-start p-5 sm:p-6 xl:col-span-7">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Common Written Feedback Themes</h2><p class="admin-card-subtitle">Recurring topics in administrator-reviewed open-ended responses.</p></div><a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'feedback'])) }}" class="admin-detail-link">View all comments</a></div>
                        @if (count($analytics['feedbackThemes']) > 0)
                            <div class="mt-5 grid gap-3">
                                @foreach (array_slice($analytics['feedbackThemes'], 0, 5) as $index => $theme)
                                    @php($themePercentage = $writtenFeedbackCount > 0 ? round(($theme['count'] / $writtenFeedbackCount) * 100, 1) : null)
                                    <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-3 gap-y-2 text-xs"><span class="grid h-6 w-6 place-items-center rounded-full bg-[#edf3ff] font-extrabold text-[#1457ff]">{{ $index + 1 }}</span><span class="font-semibold text-[#334155]">{{ $theme['label'] }}</span><span class="font-bold text-[#52658b]">{{ $themePercentage === null ? 'N/A' : rtrim(rtrim(number_format($themePercentage, 1), '0'), '.').'%' }} · {{ $theme['count'] }} {{ Str::plural('response', $theme['count']) }}</span><span class="col-start-2 col-span-2 h-2 overflow-hidden rounded-full bg-[#e9eef7]"><span class="block h-full rounded-full bg-[#1457ff]" style="width: {{ min(100, (float) ($themePercentage ?? 0)) }}%"></span></span></div>
                                @endforeach
                            </div>
                        @elseif ($writtenFeedbackCount > 0)
                            <div class="mt-5 rounded-2xl border border-dashed border-[#b8c9e7] bg-[#fbfcff] px-5 py-8 text-center"><p class="text-sm font-bold text-[#334155]">Written feedback is available, but themes have not been reviewed yet.</p><p class="mt-1 text-xs text-[#71809d]">Review comments in the Written Feedback Themes tab to build this summary.</p></div>
                        @else
                            <div class="mt-5 rounded-2xl border border-dashed border-[#dce5f2] px-5 py-8 text-center text-sm text-[#71809d]">No written feedback was submitted in this period.</div>
                        @endif
                    </article>

                    <article class="admin-insight min-w-0 self-start p-5 sm:p-6 xl:col-span-5">
                        <div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-amber-500 shadow-sm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18h6M10 22h4" /><path d="M8.5 14.5a6 6 0 1 1 7 0C14.5 15.2 14 16 14 17h-4c0-1-.5-1.8-1.5-2.5Z" /></svg></span><h2 class="admin-card-title">Key Insights</h2></div>
                        @if (count($analytics['insights']) > 0)
                            <ul class="mt-5 grid gap-3">
                                @foreach ($analytics['insights'] as $insight)
                                    <li class="grid grid-cols-[auto_minmax(0,1fr)] gap-2.5 text-sm leading-6 text-[#52658b]"><svg class="mt-1 h-4 w-4 text-[#1457ff]" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10" /><path d="m8 12 2.5 2.5L16 9" fill="none" stroke="white" stroke-width="2" /></svg><span>{{ $insight }}</span></li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-5 text-sm leading-6 text-[#52658b]">Insights will appear once survey responses are collected.</p>
                        @endif
                    </article>
                </section>
            @else
                <section class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_19rem]">
                    <div class="grid min-w-0 gap-4">
                        @forelse ($feedback as $answer)
                            <article class="admin-card p-5 sm:p-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><p class="text-sm font-semibold leading-6 text-[#334155]">{{ $answer->answer_value }}</p><span class="shrink-0 text-xs font-bold text-[#71809d]">{{ $answer->response?->created_at?->setTimezone($analytics['meta']['timezone'])->format('M j, Y') }}</span></div>
                                <form method="POST" action="{{ route('admin.feedback.review', $answer) }}" class="mt-4 border-t border-[#edf1f7] pt-4">
                                    @csrf
                                    @method('PUT')
                                    <fieldset><legend class="text-xs font-extrabold text-[#334155]">Administrator-reviewed themes</legend><div class="mt-3 flex flex-wrap gap-2">@foreach ($themes as $theme)@php($checked = $answer->themeAssignments->contains('feedback_theme_id', $theme->id))<label class="cursor-pointer"><input class="peer sr-only" type="checkbox" name="themes[]" value="{{ $theme->key }}" @checked($checked)><span class="inline-flex rounded-full border border-[#dce5f2] bg-white px-3 py-2 text-xs font-bold text-[#52658b] transition peer-checked:border-[#1457ff] peer-checked:bg-[#edf3ff] peer-checked:text-[#1457ff] peer-focus-visible:ring-2 peer-focus-visible:ring-[#1457ff]">{{ $theme->label }}</span></label>@endforeach</div></fieldset>
                                    <div class="mt-4 flex items-center justify-between gap-3"><p class="text-xs text-[#71809d]">Saved reviews update the theme summary; comments remain unchanged.</p><button class="shrink-0 rounded-lg bg-[#1457ff] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#0f46cf]">Save review</button></div>
                                </form>
                            </article>
                        @empty
                            <div class="admin-card px-5 py-14 text-center"><p class="text-base font-extrabold text-[#334155]">No written feedback</p><p class="mt-2 text-sm text-[#71809d]">No comments match this period and filter.</p></div>
                        @endforelse
                        <div>{{ $feedback->links() }}</div>
                    </div>

                    <aside class="admin-card h-fit p-5 lg:sticky lg:top-6">
                        <h2 class="admin-card-title">Feedback Themes</h2>
                        <p class="admin-card-subtitle">Counts include administrator-reviewed comments only.</p>
                        <div class="mt-4 grid gap-3">
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-3 text-xs font-bold text-amber-800"><span>Uncategorized / Needs review</span><span class="rounded-full bg-white px-2 py-1">{{ $uncategorizedCount }}</span></div>
                            @foreach ($themes as $theme)<a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'feedback', 'theme' => $theme->key])) }}" class="flex items-center justify-between gap-3 rounded-xl border border-[#e5ebf5] px-3 py-3 text-xs font-bold text-[#52658b] hover:border-[#1457ff] hover:text-[#1457ff]"><span>{{ $theme->label }}</span><span class="rounded-full bg-[#edf3ff] px-2 py-1 text-[#1457ff]">{{ (int) ($themeCounts[$theme->key] ?? 0) }}</span></a>@endforeach
                        </div>
                    </aside>
                </section>
            @endif
        </div>

        <div x-cloak x-show="rowTooltip" @click="rowTooltip = null" class="fixed inset-0 z-50 grid place-items-center bg-[#0b1739]/25 p-4" role="presentation"><div @click.stop class="w-full max-w-sm rounded-2xl bg-[#0b1739] p-4 text-white shadow-2xl" role="dialog" aria-modal="true" aria-label="Chart value"><p class="text-sm font-bold" x-text="rowTooltip?.label"></p><p class="mt-2 text-xs leading-5 text-blue-100"><span x-text="rowTooltip?.count"></span> responses · <span x-text="formatPercent(rowTooltip?.percentage)"></span><br>Of <span x-text="rowTooltip?.denominator"></span> responses answering this question</p><button type="button" @click="rowTooltip = null" class="mt-3 rounded-lg bg-white px-3 py-2 text-xs font-bold text-[#0b1739]">Close</button></div></div>
    </div>
</x-app-layout>
