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
        class="relative min-h-screen overflow-x-hidden bg-[#f5f8ff]"
    >
        <div class="mx-auto w-full max-w-[1600px] px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
            <header class="grid gap-5 2xl:grid-cols-[minmax(0,1fr)_auto] 2xl:items-end">
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#1457ff]">Survey analytics</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.035em] text-[#0b1739] sm:text-[2rem]">Conversation Room Feedback Survey</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#64748b]">Understand students' experience, identify recurring barriers, and improve the Conversation Room with evidence.</p>
                </div>

                <div class="flex min-w-0 flex-col gap-3 xl:items-end">
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex rounded-xl border border-[#dce5f2] bg-white p-1 shadow-sm" aria-label="Reporting period">
                            <template x-for="preset in presets" :key="preset.value">
                                <button type="button" @click="applyPreset(preset.value)" :disabled="loading" :class="filter.preset === preset.value ? 'bg-[#1457ff] text-white shadow-sm' : 'text-[#52658b] hover:bg-[#f1f5ff]'" class="rounded-lg px-3 py-2 text-xs font-extrabold transition disabled:cursor-wait disabled:opacity-60 sm:px-4" x-text="preset.label"></button>
                            </template>
                        </div>

                        <div class="relative" @keydown.escape.stop="closeCalendar()">
                            <button x-ref="calendarTrigger" type="button" @click="calendarOpen = !calendarOpen" :aria-expanded="calendarOpen" aria-haspopup="dialog" aria-label="Choose date or date range" title="Choose date or date range" class="inline-flex h-11 items-center gap-2 rounded-xl border border-[#dce5f2] bg-white px-3 text-xs font-extrabold text-[#334a75] shadow-sm transition hover:border-[#b8c9e7] hover:text-[#1457ff] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" /></svg>
                                <span class="hidden sm:inline" x-text="filter.preset === 'custom' ? 'Custom' : 'Choose dates'"></span>
                            </button>

                            <div x-cloak x-show="calendarOpen" x-transition.origin.top.right @click.outside="closeCalendar()" role="dialog" aria-modal="true" aria-label="Choose reporting dates" class="fixed inset-x-3 top-20 z-50 rounded-2xl border border-[#dce5f2] bg-white p-4 shadow-[0_24px_70px_rgba(11,23,57,0.22)] sm:absolute sm:inset-x-auto sm:right-0 sm:top-14 sm:w-[23rem]">
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

                        @if ($versions->count() > 1)
                            <label class="sr-only" for="survey-version">Survey version</label>
                            <select id="survey-version" x-model.number="filter.version" @change="refresh()" class="h-11 rounded-xl border-[#dce5f2] bg-white py-0 pl-3 pr-8 text-xs font-extrabold text-[#334a75] shadow-sm focus:border-[#1457ff] focus:ring-[#1457ff]">
                                @foreach ($versions as $version)
                                    <option value="{{ $version->number }}">Version {{ $version->number }} · {{ ucfirst($version->status) }}</option>
                                @endforeach
                            </select>
                        @endif

                        <form method="POST" action="{{ route('admin.reports.pdf') }}" @submit="exporting = true; window.setTimeout(() => exporting = false, 2500)">
                            @csrf
                            <input type="hidden" name="preset" :value="filter.preset"><input type="hidden" name="start" :value="filter.start"><input type="hidden" name="end" :value="filter.end"><input type="hidden" name="version" :value="filter.version"><input type="hidden" name="snapshot" :value="snapshotToken">
                            <button type="submit" :disabled="exporting || loading" class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#1457ff] px-4 text-xs font-extrabold text-white shadow-sm transition hover:bg-[#0f46cf] disabled:cursor-wait disabled:opacity-60">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5" /><path d="M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" /></svg><span x-text="exporting ? 'Preparing report...' : 'Download PDF'"></span>
                            </button>
                        </form>
                        <x-admin.notification-bell :alert-data="$alertData" />
                    </div>
                    <p class="text-xs font-semibold text-[#64748b]"><span x-text="analytics.meta.filter.label"></span><span aria-hidden="true"> · </span><span x-text="analytics.meta.timezone"></span><span aria-hidden="true"> · </span><span>Version <span x-text="analytics.meta.version"></span></span></p>
                </div>
            </header>

            <div x-cloak x-show="errorMessage" class="mt-5 flex flex-col gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 sm:flex-row sm:items-center sm:justify-between" role="alert"><span x-text="errorMessage"></span><button type="button" @click="refresh()" class="w-fit rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white">Retry</button></div>

            <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Survey overview">
                <article class="admin-card min-h-[8.5rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[#e8f0ff] text-[#1457ff]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /></svg></span><div class="min-w-0"><p class="text-sm font-semibold text-[#52658b]">Responses in period</p><p class="mt-1 text-3xl font-extrabold tracking-[-0.04em] text-[#0b1739]" x-text="formatNumber(animated.responses)"></p><p class="mt-1 text-xs leading-5 text-[#71809d]">Completed, non-demo submissions</p></div></div></article>
                <article class="admin-card min-h-[8.5rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-emerald-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 12.5 10.5 15 16 9.5" /><circle cx="12" cy="12" r="9" /></svg></span><div class="min-w-0"><p class="text-sm font-semibold text-[#52658b]">Positive activity feedback</p><p class="mt-1 text-3xl font-extrabold tracking-[-0.04em] text-[#0b1739]" x-text="formatPercent(animated.positive)"></p><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-text="analytics.overview.positiveActivity.count"></span> of <span x-text="analytics.overview.positiveActivity.denominator"></span> valid Q6 answers</p></div></div></article>
                <article class="admin-card min-h-[8.5rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-violet-50 text-violet-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 18h8M10 22h4" /><path d="M9 13a5 5 0 1 1 6 0c-1 .7-1.5 1.7-1.5 3h-3c0-1.3-.5-2.3-1.5-3Z" /></svg></span><div class="min-w-0"><p class="text-sm font-semibold text-[#52658b]">Main attendance barrier</p><p class="mt-1 line-clamp-2 text-lg font-extrabold leading-6 text-[#0b1739]" x-text="analytics.overview.mainBarrier.label"></p><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-text="analytics.overview.mainBarrier.count"></span> responses · <span x-text="formatPercent(analytics.overview.mainBarrier.percentage)"></span></p></div></div></article>
                <article class="admin-card min-h-[8.5rem] p-5"><div class="flex items-start gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-amber-50 text-amber-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg></span><div class="min-w-0"><p class="text-sm font-semibold text-[#52658b]">Most preferred time</p><p class="mt-1 line-clamp-2 text-lg font-extrabold leading-6 text-[#0b1739]" x-text="analytics.overview.preferredTime.label"></p><p class="mt-1 text-xs leading-5 text-[#71809d]"><span x-text="analytics.overview.preferredTime.count"></span> responses · <span x-text="formatPercent(analytics.overview.preferredTime.percentage)"></span></p></div></div></article>
            </section>

            <div x-show="loading" class="mt-5 grid gap-4 lg:grid-cols-12" aria-live="polite" aria-label="Updating analytics"><div class="h-72 animate-pulse rounded-[1.25rem] bg-white/70 lg:col-span-7"></div><div class="h-72 animate-pulse rounded-[1.25rem] bg-white/70 lg:col-span-5"></div></div>

            <div :class="loading ? 'pointer-events-none opacity-30' : 'opacity-100'" class="mt-5 grid min-w-0 gap-5 transition-opacity">
                <section class="grid min-w-0 gap-5 xl:grid-cols-12">
                    <article class="admin-card min-w-0 p-5 sm:p-6 xl:col-span-7">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><h2 class="admin-card-title">Responses Over Time</h2><p class="admin-card-subtitle"><span x-text="analytics.series.label"></span> · <span x-text="analytics.series.timezone"></span></p></div><span class="rounded-full bg-[#edf3ff] px-3 py-1.5 text-xs font-bold text-[#1457ff]" x-text="analytics.series.total + ' total'"></span></div>
                        <div class="relative mt-4 h-[18rem] min-w-0"><canvas x-ref="responsesChart" aria-label="Responses over time chart"></canvas></div>
                        <details class="mt-3 text-xs text-[#52658b]"><summary class="cursor-pointer font-bold text-[#1457ff]">Accessible data table</summary><div class="mt-2 max-h-44 overflow-auto rounded-lg border border-[#e5ebf5]"><table class="w-full text-left"><thead class="sticky top-0 bg-[#f5f8ff]"><tr><th class="px-3 py-2">Period</th><th class="px-3 py-2 text-right">Responses</th></tr></thead><tbody><template x-for="point in analytics.series.points" :key="point.key"><tr class="border-t border-[#edf1f7]"><td class="px-3 py-2" x-text="point.fullLabel"></td><td class="px-3 py-2 text-right font-bold" x-text="point.value"></td></tr></template></tbody></table></div></details>
                    </article>

                    <article class="admin-card min-w-0 p-5 sm:p-6 xl:col-span-5">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Attendance Frequency</h2><p class="admin-card-subtitle">How often respondents attended the Conversation Room</p></div><a :href="detailUrl('attendance_frequency')" class="admin-detail-link">View details</a></div>
                        <div class="mt-4 grid items-center gap-4 md:grid-cols-[minmax(12rem,0.9fr)_minmax(0,1.1fr)]"><div class="relative mx-auto h-[15rem] w-full max-w-[16rem]"><canvas x-ref="attendanceChart" aria-label="Attendance frequency doughnut chart"></canvas></div><div class="grid gap-2"><template x-for="(item, index) in distribution(analytics.dashboard.attendance).options" :key="item.id"><div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2 text-xs"><span class="h-2.5 w-2.5 rounded-full" :style="'background:' + colors[index % colors.length]"></span><span class="truncate text-[#52658b]" :title="item.label" x-text="item.label"></span><span class="font-extrabold text-[#0b1739]"><span x-text="item.count"></span> · <span x-text="formatPercent(item.percentage)"></span></span></div></template></div></div>
                    </article>
                </section>

                <section class="grid min-w-0 gap-5 lg:grid-cols-2">
                    <article class="admin-card min-w-0 p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Top Attendance Barriers</h2><p class="admin-card-subtitle">Q3 · Multiple selections allowed; percentages may exceed 100%</p></div><a :href="detailUrl('attendance_reasons')" class="admin-detail-link">View all reasons</a></div><div class="relative mt-4 h-[19rem] min-w-0"><canvas x-ref="barriersChart" aria-label="Top attendance barriers horizontal bar chart"></canvas></div></article>

                    <article class="admin-card min-w-0 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Preferred Session Format</h2><p class="admin-card-subtitle">Which format respondents prefer</p></div><a :href="detailUrl('session_format')" class="admin-detail-link">View details</a></div>
                        <div class="mt-5 grid gap-3"><template x-for="(item, index) in rankedOptions(analytics.dashboard.format)" :key="item.id"><button type="button" @click="showRowTooltip(item, distribution(analytics.dashboard.format).answered)" class="grid w-full grid-cols-[auto_auto_minmax(0,1fr)_auto] items-center gap-3 rounded-xl border border-[#e5ebf5] px-3 py-2.5 text-left transition hover:border-[#b8c9e7] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff]"><span class="grid h-9 w-9 place-items-center rounded-xl text-xs font-extrabold" :style="'background:' + softColors[index % softColors.length] + ';color:' + colors[index % colors.length]" x-text="index + 1"></span><span class="rounded-full bg-[#f5f8ff] px-2 py-1 text-xs font-extrabold text-[#1457ff]" x-text="formatPercent(item.percentage)"></span><span class="min-w-0 text-sm font-bold text-[#334155]" x-text="item.label"></span><span class="text-xs font-extrabold text-[#0b1739]" x-text="item.count"></span></button></template></div>
                    </article>
                </section>

                <section class="grid min-w-0 gap-5 xl:grid-cols-3">
                    <article class="admin-card min-w-0 p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Preferred Time to Attend</h2><p class="admin-card-subtitle">Preferred local periods, not converted schedules</p></div><a :href="detailUrl('preferred_session_time')" class="admin-detail-link">View details</a></div><div class="relative mt-4 h-[17rem] min-w-0"><canvas x-ref="preferredTimeChart" aria-label="Preferred time vertical bar chart"></canvas></div></article>
                    <article class="admin-card min-w-0 p-5 sm:p-6"><div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Likelihood to Attend Again</h2><p class="admin-card-subtitle">Stated intention, not measured future attendance</p></div><a :href="detailUrl('attendance_likelihood')" class="admin-detail-link">View details</a></div><div class="relative mt-4 h-[17rem] min-w-0"><canvas x-ref="likelihoodChart" aria-label="Likelihood to attend again bar chart"></canvas></div></article>
                    <article class="admin-card min-w-0 p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3"><div><h2 class="admin-card-title">Recent Written Feedback</h2><p class="admin-card-subtitle">Latest non-empty comments in this period</p></div><a :href="feedbackUrl()" class="admin-detail-link">View all</a></div>
                        <div class="mt-4 grid gap-2.5"><template x-for="feedback in analytics.feedback" :key="feedback.id"><a :href="feedbackUrl()" class="group rounded-xl border border-[#e5ebf5] bg-[#fbfcff] px-3.5 py-3 transition hover:border-[#b8c9e7]"><p class="line-clamp-2 text-sm font-semibold leading-5 text-[#334155]" x-text="feedback.text"></p><p class="mt-2 text-xs font-bold text-[#71809d]" x-text="feedback.date"></p></a></template><div x-show="analytics.feedback.length === 0" class="rounded-xl border border-dashed border-[#cbd5e1] px-4 py-8 text-center"><p class="text-sm font-bold text-[#334155]">No written feedback</p><p class="mt-1 text-xs text-[#64748b]">No comments were submitted in this period.</p></div></div>
                    </article>
                </section>
            </div>

            <div x-cloak x-show="rowTooltip" @click="rowTooltip = null" class="fixed inset-0 z-50 grid place-items-center bg-[#0b1739]/25 p-4" role="presentation"><div @click.stop class="w-full max-w-sm rounded-2xl bg-[#0b1739] p-4 text-white shadow-2xl" role="dialog" aria-modal="true" aria-label="Chart value"><p class="text-sm font-bold" x-text="rowTooltip?.label"></p><p class="mt-2 text-xs leading-5 text-blue-100"><span x-text="rowTooltip?.count"></span> responses · <span x-text="formatPercent(rowTooltip?.percentage)"></span><br>Of <span x-text="rowTooltip?.denominator"></span> responses answering this question</p><button type="button" @click="rowTooltip = null" class="mt-3 rounded-lg bg-white px-3 py-2 text-xs font-bold text-[#0b1739]">Close</button></div></div>
        </div>
    </div>
</x-app-layout>
