<x-app-layout>
    <div
        x-data="dashboardAnalytics({{
            Js::from([
                'analytics' => $analytics,
                'snapshotToken' => $snapshotToken,
                'dataUrl' => route('admin.analytics.show'),
                'dashboardUrl' => route('dashboard'),
                'commonAnswersUrl' => route('admin.common-answers'),
            ])
        }})"
        class="admin-dashboard relative min-h-screen overflow-x-hidden"
    >
        <div class="mx-auto w-full max-w-[1600px] px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
            <header class="admin-dashboard-header grid gap-5 2xl:grid-cols-[minmax(0,1fr)_auto] 2xl:items-end">
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#1457ff]">Survey analytics</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.035em] text-[#0b1739] sm:text-[2rem]">Conversation Room Feedback Survey</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#64748b]">Understand students' experience, identify recurring barriers, and improve the Conversation Room with evidence.</p>
                </div>

                <div class="flex min-w-0 flex-col gap-3 xl:items-end">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="admin-filter-group inline-flex rounded-xl p-1" aria-label="Reporting period">
                            <template x-for="preset in presets" :key="preset.value">
                                <button type="button" @click="applyPreset(preset.value)" :disabled="loading" :class="filter.preset === preset.value ? 'bg-[#1457ff] text-white shadow-sm' : 'text-[#52658b] hover:bg-[#f1f5ff]'" class="rounded-lg px-3 py-2 text-xs font-extrabold transition disabled:cursor-wait disabled:opacity-60 sm:px-4" x-text="preset.label"></button>
                            </template>
                        </div>

                        <div class="relative" @keydown.escape.stop="closeCalendar()">
                            <button x-ref="calendarTrigger" type="button" @click="calendarOpen = !calendarOpen" :aria-expanded="calendarOpen" aria-haspopup="dialog" aria-label="Choose date or date range" title="Choose date or date range" class="admin-control inline-flex h-11 items-center gap-2 rounded-xl px-3 text-xs font-extrabold text-[#334a75] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" /></svg>
                                <span class="hidden sm:inline" x-text="filter.preset === 'custom' ? 'Custom' : 'Choose dates'"></span>
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

                        <form method="POST" action="{{ route('admin.reports.pdf') }}" @submit="exporting = true; window.setTimeout(() => exporting = false, 2500)">
                            @csrf
                            <input type="hidden" name="preset" :value="filter.preset"><input type="hidden" name="start" :value="filter.start"><input type="hidden" name="end" :value="filter.end"><input type="hidden" name="snapshot" :value="snapshotToken">
                            <button type="submit" :disabled="exporting || loading" class="admin-primary-action inline-flex h-11 items-center gap-2 rounded-xl px-4 text-xs font-extrabold text-white disabled:cursor-wait disabled:opacity-60">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5" /><path d="M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" /></svg><span x-text="exporting ? 'Preparing report...' : 'Download PDF'"></span>
                            </button>
                        </form>
                        <x-admin.notification-bell :alert-data="$alertData" />
                    </div>
                    <p class="text-xs font-semibold text-[#64748b]"><span x-text="analytics.meta.filter.label"></span><span aria-hidden="true"> · </span><span x-text="analytics.meta.timezone"></span><span aria-hidden="true"> · </span><span x-text="analytics.meta.scope"></span></p>
                </div>
            </header>

            <div x-cloak x-show="errorMessage" class="mt-5 flex flex-col gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 sm:flex-row sm:items-center sm:justify-between" role="alert"><span x-text="errorMessage"></span><button type="button" @click="refresh()" class="w-fit rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white">Retry</button></div>

            <section x-show="Number(analytics.overview.mainBarrier.count ?? 0) > 0" class="insight-banner mt-5" aria-label="Key insight spotlight">
                <x-admin.analytics-waves />
                <div class="insight-banner__content">
                    <span class="insight-banner__icon" aria-hidden="true"><svg class="h-[1.35rem] w-[1.35rem]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M8.5 14.5a6 6 0 1 1 7 0C14.5 15.2 14 16 14 17h-4c0-1-.5-1.8-1.5-2.5Z" /><path d="M9 21h6M9 18h6" /></svg></span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[#52658b]">Key insight</p>
                        <h2 class="mt-0.5 text-[1.02rem] font-extrabold leading-6 text-[#0b1739]">The leading attendance barrier is <span x-text="analytics.overview.mainBarrier.label"></span>.</h2>
                        <p class="mt-0.5 text-[0.8rem] leading-5 text-[#64748b]"><span class="font-extrabold text-[#1457ff]" x-text="formatPercent(analytics.overview.mainBarrier.percentage)"></span> of respondents answering this question selected it.</p>
                        <a :href="detailUrl('attendance_reasons')" class="admin-detail-link mt-2">Explore the evidence</a>
                    </div>
                </div>
            </section>

            <section x-show="Number(analytics.overview.responses ?? 0) === 0" class="insight-banner mt-5" aria-label="No survey responses yet">
                <x-admin.analytics-waves />
                <div class="insight-banner__content">
                    <span class="insight-banner__icon" aria-hidden="true"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2" /></svg></span>
                    <div class="min-w-0">
                        <h2 class="text-[1.05rem] font-extrabold leading-6 text-[#0b1739]">No survey responses yet</h2>
                        <p class="mt-0.5 text-[0.8rem] leading-5 text-[#64748b]">Analytics will appear here once students start submitting feedback.</p>
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Survey overview">
                <article class="admin-card admin-kpi-card admin-kpi-blue p-5"><div class="flex items-start gap-4"><span class="admin-kpi-icon bg-gradient-to-br from-[#edf4ff] to-[#dceaff] text-[#1457ff]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /></svg></span><div class="min-w-0"><p class="admin-kpi-label">Responses in period</p><p class="admin-kpi-value mt-1" x-text="formatNumber(animated.responses)"></p><p class="mt-1 text-xs leading-5 text-[#71809d]" x-text="Number(analytics.overview.responses ?? 0) > 0 ? 'Completed, non-demo submissions' : 'No responses yet'"></p></div></div></article>
                <article class="admin-card admin-kpi-card admin-kpi-green p-5"><div class="flex items-start gap-4"><span class="admin-kpi-icon bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 12.5 10.5 15 16 9.5" /><circle cx="12" cy="12" r="9" /></svg></span><div class="min-w-0"><p class="admin-kpi-label">Positive activity feedback</p><p class="admin-kpi-value mt-1" x-text="Number(analytics.overview.positiveActivity.denominator ?? 0) > 0 ? formatPercent(animated.positive) : 'No data'"></p><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-show="Number(analytics.overview.positiveActivity.denominator ?? 0) > 0"><span x-text="analytics.overview.positiveActivity.count"></span> of <span x-text="analytics.overview.positiveActivity.denominator"></span> valid Q6 answers</span><span x-show="Number(analytics.overview.positiveActivity.denominator ?? 0) === 0">Respondents needed</span></p></div></div></article>
                <article class="admin-card admin-kpi-card admin-kpi-amber p-5"><div class="flex items-start gap-4"><span class="admin-kpi-icon bg-gradient-to-br from-amber-50 to-orange-100 text-amber-600"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 18h8M10 22h4" /><path d="M9 13a5 5 0 1 1 6 0c-1 .7-1.5 1.7-1.5 3h-3c0-1.3-.5-2.3-1.5-3Z" /></svg></span><div class="min-w-0"><p class="admin-kpi-label">Main attendance barrier</p><p class="admin-kpi-value admin-kpi-value--compact mt-1" x-text="Number(analytics.overview.mainBarrier.count ?? 0) > 0 ? analytics.overview.mainBarrier.label : 'N/A'"></p><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-show="Number(analytics.overview.mainBarrier.count ?? 0) > 0"><span x-text="analytics.overview.mainBarrier.count"></span> responses · <span x-text="formatPercent(analytics.overview.mainBarrier.percentage)"></span></span><span x-show="Number(analytics.overview.mainBarrier.count ?? 0) === 0">No responses yet</span></p></div></div></article>
                <article class="admin-card admin-kpi-card admin-kpi-lilac p-5"><div class="flex items-start gap-4"><span class="admin-kpi-icon bg-gradient-to-br from-violet-50 to-fuchsia-100 text-violet-600"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg></span><div class="min-w-0"><p class="admin-kpi-label">Most preferred time</p><p class="admin-kpi-value admin-kpi-value--compact mt-1" x-text="Number(analytics.overview.preferredTime.count ?? 0) > 0 ? analytics.overview.preferredTime.label : 'N/A'"></p><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-show="Number(analytics.overview.preferredTime.count ?? 0) > 0"><span x-text="analytics.overview.preferredTime.count"></span> responses · <span x-text="formatPercent(analytics.overview.preferredTime.percentage)"></span></span><span x-show="Number(analytics.overview.preferredTime.count ?? 0) === 0">No responses yet</span></p></div></div></article>
            </section>

            <div x-show="loading" class="mt-5 grid gap-4 lg:grid-cols-12" aria-live="polite" aria-label="Updating analytics"><div class="h-72 animate-pulse rounded-[1.25rem] bg-white/70 lg:col-span-7"></div><div class="h-72 animate-pulse rounded-[1.25rem] bg-white/70 lg:col-span-5"></div></div>

            <div :class="loading ? 'pointer-events-none opacity-30' : 'opacity-100'" class="mt-5 grid min-w-0 gap-5 transition-opacity">
                <section class="grid min-w-0 gap-5 xl:grid-cols-12">
                    <article class="admin-card min-w-0 p-5 sm:p-6 xl:col-span-7">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="admin-card-title">Responses Over Time</h2><p class="admin-card-subtitle"><span x-text="analytics.series.label"></span> · <span x-text="analytics.series.timezone"></span></p></div><span class="rounded-full bg-[#edf3ff] px-3 py-1.5 text-xs font-bold text-[#1457ff]" x-text="analytics.series.total + ' total'"></span></div>
                        <div class="relative mt-4 h-[18rem] min-w-0">
                            <template x-if="hasSeriesData"><canvas x-ref="responsesChart" aria-label="Responses over time chart"></canvas></template>
                            <div x-show="!hasSeriesData" class="admin-chart-empty grid h-full place-content-center justify-items-center gap-2 text-center" role="status">
                                <span class="admin-empty-icon" aria-hidden="true"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2" /></svg></span>
                                <p class="admin-empty-title">No data available yet</p>
                                <p class="admin-empty-description max-w-[22rem]">Survey responses will appear here once students start submitting feedback.</p>
                            </div>
                        </div>
                        <details class="mt-3 text-xs text-[#52658b]"><summary class="cursor-pointer font-bold text-[#1457ff]">Accessible data table</summary><div class="mt-2 max-h-44 overflow-auto rounded-lg border border-[#e5ebf5]"><table class="w-full text-left"><thead class="sticky top-0 bg-[#f5f8ff]"><tr><th class="px-3 py-2">Period</th><th class="px-3 py-2 text-right">Responses</th></tr></thead><tbody><template x-for="point in analytics.series.points" :key="point.key"><tr class="border-t border-[#edf1f7]"><td class="px-3 py-2" x-text="point.fullLabel"></td><td class="px-3 py-2 text-right font-bold" x-text="point.value"></td></tr></template></tbody></table></div></details>
                    </article>

                    <article class="admin-card min-w-0 p-5 sm:p-6 xl:col-span-5">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Attendance Frequency</h2><p class="admin-card-subtitle">How often respondents attended the Conversation Room</p></div><a :href="detailUrl('attendance_frequency')" class="admin-detail-link">View details</a></div>
                        <div class="mt-4 grid items-center gap-4 md:grid-cols-[minmax(12rem,0.9fr)_minmax(0,1.1fr)]">
                            <div class="relative mx-auto grid h-[15rem] w-full max-w-[16rem] place-items-center">
                                <template x-if="hasAnswers(analytics.dashboard.attendance)"><div class="absolute inset-0"><canvas x-ref="attendanceChart" aria-label="Attendance frequency doughnut chart"></canvas></div></template>
                                <div x-show="!hasAnswers(analytics.dashboard.attendance)" class="grid h-full w-full place-items-center" role="status">
                                    <span class="admin-donut-empty" aria-hidden="true"><span class="admin-donut-icon"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg></span></span>
                                    <span class="sr-only">No attendance data yet</span>
                                </div>
                                <span x-show="!hasAnswers(analytics.dashboard.attendance)" class="z-10 -mt-1 text-center" role="status">
                                    <span class="block text-sm font-bold text-[#0b1739]">No responses yet</span>
                                    <span class="mt-0.5 block text-[0.7rem] leading-4 text-[#71809d]">Attendance data will appear here after submissions.</span>
                                </span>
                            </div>
                            <div class="grid gap-2"><template x-for="(item, index) in distribution(analytics.dashboard.attendance).options" :key="item.id"><div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2 text-xs"><span class="h-2.5 w-2.5 rounded-full" :style="'background:' + colors[index % colors.length]"></span><span class="truncate text-[#52658b]" :title="item.label" x-text="item.label"></span><span class="font-extrabold text-[#0b1739]" x-text="distribution(analytics.dashboard.attendance).answered > 0 ? (item.count + ' · ' + formatPercent(item.percentage)) : '—'"></span></div></template></div>
                        </div>
                    </article>
                </section>

                <section class="grid min-w-0 gap-5 lg:grid-cols-2">
                    <article class="admin-card min-w-0 p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Top Attendance Barriers</h2><p class="admin-card-subtitle">Q3 · Multiple selections allowed; percentages may exceed 100%</p></div><a :href="detailUrl('attendance_reasons')" class="admin-detail-link">View all reasons</a></div>
                        <div class="relative mt-4 h-[19rem] min-w-0">
                            <template x-if="hasAnswers(analytics.dashboard.barriers)"><canvas x-ref="barriersChart" aria-label="Top attendance barriers horizontal bar chart"></canvas></template>
                            <div x-show="!hasAnswers(analytics.dashboard.barriers)" class="grid h-full place-items-center"><x-admin.analytics-empty icon="list" title="No data available yet" description="Responses will appear here once students start submitting the survey." /></div>
                        </div></article>

                    <article class="admin-card min-w-0 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Preferred Session Format</h2><p class="admin-card-subtitle">Which format respondents prefer</p></div><a :href="detailUrl('session_format')" class="admin-detail-link">View details</a></div>
                        <div class="mt-5 grid gap-3">
                            <template x-if="hasAnswers(analytics.dashboard.format)">
                                <div class="grid gap-3"><template x-for="(item, index) in rankedOptions(analytics.dashboard.format)" :key="item.id"><button type="button" @click="showRowTooltip(item, distribution(analytics.dashboard.format).answered)" class="admin-data-row grid w-full grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-3 rounded-xl border px-3 py-2.5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]"><span class="grid h-9 w-9 place-items-center rounded-xl text-xs font-extrabold" :style="'background:' + softColors[index % softColors.length] + ';color:' + colors[index % colors.length]" x-text="index + 1"></span><span class="rounded-full bg-[#f5f8ff] px-2 py-1 text-xs font-extrabold text-[#1457ff]" x-text="formatPercent(item.percentage)"></span><span class="min-w-0 text-sm font-bold text-[#334155]" x-text="item.label"></span><span class="text-xs font-extrabold text-[#0b1739]" x-text="item.count"></span></button></template></div>
                            </template>
                            <div x-show="!hasAnswers(analytics.dashboard.format)" class="grid min-h-[16rem] place-items-center"><x-admin.analytics-empty icon="users" title="No data available yet" description="Preferred format data will appear here after survey submissions." /></div>
                        </div>
                    </article>
                </section>

                <section class="grid min-w-0 gap-5 xl:grid-cols-3">
                    <article class="admin-card min-w-0 p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Preferred Time to Attend</h2><p class="admin-card-subtitle">Preferred local periods, not converted schedules</p></div><a :href="detailUrl('preferred_session_time')" class="admin-detail-link">View details</a></div>
                        <div class="relative mt-4 h-[17rem] min-w-0">
                            <template x-if="hasAnswers(analytics.dashboard.preferredTime)"><canvas x-ref="preferredTimeChart" aria-label="Preferred time vertical bar chart"></canvas></template>
                            <div x-show="!hasAnswers(analytics.dashboard.preferredTime)" class="grid h-full place-items-center"><x-admin.analytics-empty icon="clock" variant="grid" title="No data available yet" description="Time preference data will appear here once students start submitting feedback." /></div>
                        </div></article>
                    <article class="admin-card min-w-0 p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Likelihood to Attend Again</h2><p class="admin-card-subtitle">Stated intention, not measured future attendance</p></div><a :href="detailUrl('attendance_likelihood')" class="admin-detail-link">View details</a></div>
                        <div class="relative mt-4 h-[17rem] min-w-0">
                            <template x-if="hasAnswers(analytics.dashboard.likelihood)"><canvas x-ref="likelihoodChart" aria-label="Likelihood to attend again bar chart"></canvas></template>
                            <div x-show="!hasAnswers(analytics.dashboard.likelihood)" class="grid h-full place-items-center"><x-admin.analytics-empty icon="chart" variant="grid" title="No data available yet" description="Responses will appear here after survey submissions." /></div>
                        </div></article>
                    <article class="admin-card min-w-0 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Recent Written Feedback</h2><p class="admin-card-subtitle">Latest non-empty comments in this period</p></div><a :href="feedbackUrl()" class="admin-detail-link">View all</a></div>
                        <div class="mt-4 grid gap-2.5">
                            <template x-for="feedback in analytics.feedback" :key="feedback.id"><a :href="feedbackUrl()" class="admin-feedback-row group rounded-xl border px-3.5 py-3"><p class="line-clamp-2 text-sm font-semibold leading-5 text-[#334155]" x-text="feedback.text"></p><p class="mt-2 text-xs font-bold text-[#71809d]" x-text="feedback.date"></p></a></template>
                            <div x-show="analytics.feedback.length === 0" class="grid min-h-[14rem] place-items-center"><x-admin.analytics-empty icon="message" title="No feedback yet" description="Student comments will appear here once responses are submitted." /></div>
                        </div>
                    </article>
                </section>
            </div>

            <div x-cloak x-show="rowTooltip" @click="rowTooltip = null" class="fixed inset-0 z-50 grid place-items-center bg-[#0b1739]/25 p-4 backdrop-blur-sm" role="presentation"><div @click.stop class="admin-popover w-full max-w-sm rounded-2xl border p-4 text-[#0b1739]" role="dialog" aria-modal="true" aria-label="Chart value"><p class="text-sm font-bold" x-text="rowTooltip?.label"></p><p class="mt-2 text-xs leading-5 text-[#52658b]"><span x-text="rowTooltip?.count"></span> responses · <span x-text="formatPercent(rowTooltip?.percentage)"></span><br>Of <span x-text="rowTooltip?.denominator"></span> responses answering this question</p><button type="button" @click="rowTooltip = null" class="admin-primary-action mt-3 rounded-lg px-3 py-2 text-xs font-bold text-white">Close</button></div></div>
        </div>
    </div>
</x-app-layout>
