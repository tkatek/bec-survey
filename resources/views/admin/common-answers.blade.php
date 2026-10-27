<x-app-layout>
    @php
        $filterQuery = $analytics['meta']['query'];
        $pdfQuery = array_merge($filterQuery, ['snapshot' => $snapshotToken]);
    @endphp

    <div class="relative min-h-screen overflow-x-hidden bg-[#f5f8ff]">
        <div class="mx-auto w-full max-w-[1600px] px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
            <header class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.12em] text-[#1457ff]">Survey analytics</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.035em] text-[#0b1739] sm:text-[2rem]">Common Answers</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#64748b]">Inspect every selected answer, denominator, written comment, and administrator-reviewed feedback theme.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard', $filterQuery) }}" class="inline-flex h-11 items-center gap-2 rounded-xl border border-[#dce5f2] bg-white px-4 text-xs font-extrabold text-[#334a75] shadow-sm hover:border-[#b8c9e7] hover:text-[#1457ff]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6" /></svg>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('admin.reports.pdf') }}">
                        @csrf
                        @foreach ($pdfQuery as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#1457ff] px-4 text-xs font-extrabold text-white shadow-sm hover:bg-[#0f46cf]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5" /><path d="M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" /></svg>
                            Download PDF
                        </button>
                    </form>
                    <x-admin.notification-bell :alert-data="$alertData" />
                </div>
            </header>

            <div class="mt-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-[#64748b]">
                <span>{{ $analytics['meta']['filter']['label'] }}</span><span aria-hidden="true">·</span>
                <span>{{ $analytics['meta']['timezone'] }}</span><span aria-hidden="true">·</span>
                <span>Version {{ $analytics['meta']['version'] }}</span><span aria-hidden="true">·</span>
                <span>{{ number_format($analytics['overview']['responses']) }} responses</span>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" role="status">{{ session('status') }}</div>
            @endif

            @if ($selectedAlert)
                <section class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5" aria-labelledby="alert-evidence-title">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-amber-700">Alert evidence</p>
                            <h2 id="alert-evidence-title" class="mt-1 text-lg font-extrabold text-[#0b1739]">{{ $selectedAlert['title'] }}</h2>
                            <p class="mt-2 max-w-4xl text-sm leading-6 text-[#52658b]">{{ $selectedAlert['explanation'] }}</p>
                            <p class="mt-2 text-xs font-bold text-[#334155]">{{ $selectedAlert['matchingCount'] }} matching of {{ $selectedAlert['denominator'] }} answering · threshold {{ $selectedAlert['threshold'] }} · {{ $selectedAlert['period'] }}</p>
                        </div>
                        <span class="w-fit rounded-full bg-white px-3 py-1.5 text-xs font-extrabold text-amber-700 shadow-sm">{{ count($selectedAlert['evidenceResponseIds']) }} supporting response IDs</span>
                    </div>
                </section>
            @endif

            <nav class="mt-6 inline-flex w-full rounded-2xl border border-[#dce5f2] bg-white p-1 shadow-sm sm:w-auto" aria-label="Answer views">
                <a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'selected'])) }}" @class(['flex-1 rounded-xl px-4 py-2.5 text-center text-sm font-extrabold transition sm:flex-none', 'bg-[#1457ff] text-white shadow-sm' => $selectedTab === 'selected', 'text-[#52658b] hover:bg-[#f1f5ff]' => $selectedTab !== 'selected'])>Selected Answers</a>
                <a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => 'feedback'])) }}" @class(['flex-1 rounded-xl px-4 py-2.5 text-center text-sm font-extrabold transition sm:flex-none', 'bg-[#1457ff] text-white shadow-sm' => $selectedTab === 'feedback', 'text-[#52658b] hover:bg-[#f1f5ff]' => $selectedTab !== 'feedback'])>Written Feedback</a>
            </nav>

            <form method="GET" action="{{ route('admin.common-answers') }}" class="admin-card mt-4 grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-[minmax(14rem,1fr)_13rem_13rem_11rem_auto]">
                <input type="hidden" name="preset" value="{{ $analytics['meta']['filter']['preset'] }}">
                <input type="hidden" name="start" value="{{ $analytics['meta']['filter']['start'] }}">
                <input type="hidden" name="end" value="{{ $analytics['meta']['filter']['end'] }}">
                <input type="hidden" name="version" value="{{ $analytics['meta']['version'] }}">
                <input type="hidden" name="tab" value="{{ $selectedTab }}">
                @if ($selectedAlert)<input type="hidden" name="alert" value="{{ $selectedAlert['id'] }}">@endif

                <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Search
                    <input name="search" value="{{ $search }}" type="search" placeholder="Question, option, or comment" class="h-11 rounded-xl border-[#dce5f2] text-sm focus:border-[#1457ff] focus:ring-[#1457ff]">
                </label>
                @if ($selectedTab === 'selected')
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Section
                        <select name="section" class="h-11 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="">All sections</option>@foreach ($sections as $section)<option value="{{ $section }}" @selected($selectedSection === $section)>{{ $section }}</option>@endforeach</select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Question
                        <select name="question" class="h-11 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="">All questions</option>@foreach ($analytics['questions'] as $question)@if ($question['type'] !== 'long_text')<option value="{{ $question['key'] }}" @selected($selectedQuestion === $question['key'])>Q{{ $question['questionOrder'] }} · {{ Str::limit($question['prompt'], 42) }}</option>@endif @endforeach</select>
                    </label>
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Order
                        <select name="sort" class="h-11 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="original" @selected($sort === 'original')>Survey order</option><option value="count" @selected($sort === 'count')>Most common first</option></select>
                    </label>
                @else
                    <label class="grid gap-1.5 text-xs font-bold text-[#334155]">Feedback theme
                        <select name="theme" class="h-11 rounded-xl border-[#dce5f2] py-0 text-sm focus:border-[#1457ff] focus:ring-[#1457ff]"><option value="">All themes</option>@foreach ($themes as $theme)<option value="{{ $theme->key }}" @selected($selectedTheme === $theme->key)>{{ $theme->label }} ({{ (int) ($themeCounts[$theme->key] ?? 0) }})</option>@endforeach</select>
                    </label>
                @endif
                <div class="flex items-end gap-2"><button class="h-11 rounded-xl bg-[#1457ff] px-5 text-xs font-extrabold text-white hover:bg-[#0f46cf]">Apply filters</button><a href="{{ route('admin.common-answers', array_merge($filterQuery, ['tab' => $selectedTab])) }}" class="grid h-11 place-items-center rounded-xl border border-[#dce5f2] px-4 text-xs font-bold text-[#52658b]">Reset</a></div>
            </form>

            @if ($selectedTab === 'selected')
                <div class="mt-5 grid gap-4">
                    @forelse ($selectedQuestions as $question)
                        <details class="admin-card group overflow-hidden" @if ($selectedQuestion === $question['key'] || $selectedQuestions->count() === 1) open @endif>
                            <summary class="flex cursor-pointer list-none items-start justify-between gap-4 p-5 sm:p-6">
                                <div class="flex min-w-0 gap-3 sm:gap-4">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#0b1739] text-xs font-extrabold text-white">{{ str_pad((string) $question['questionOrder'], 2, '0', STR_PAD_LEFT) }}</span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-extrabold text-[#1457ff]">{{ $question['section'] }}</p>
                                        <h2 class="mt-1 text-base font-extrabold leading-6 text-[#0b1739] sm:text-lg">{{ $question['prompt'] }}</h2>
                                        <p class="mt-1 text-xs leading-5 text-[#71809d]">
                                            {{ $question['answered'] }} answered · {{ $question['skipped'] }} skipped
                                            @if ($question['isMultiSelect'])
                                                · Multiple selections allowed; percentages may exceed 100%.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <svg class="mt-2 h-5 w-5 shrink-0 text-[#71809d] transition group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6" /></svg>
                            </summary>
                            <div class="border-t border-[#edf1f7] px-5 pb-6 pt-5 sm:px-6">
                                @if (count($question['leaders']) > 0)
                                    <div class="mb-4 flex flex-wrap items-center gap-2"><span class="text-xs font-bold text-[#52658b]">Most common:</span>@foreach ($question['leaders'] as $leader)<span class="rounded-full bg-[#edf3ff] px-3 py-1 text-xs font-extrabold text-[#1457ff]">{{ $leader['label'] }} · {{ $leader['count'] }}</span>@endforeach</div>
                                @endif
                                <div class="grid gap-5 @if(count($question['distributions']) > 1) xl:grid-cols-2 @endif">
                                    @foreach ($question['distributions'] as $distribution)
                                        <section>
                                            @if ($distribution['group'])<h3 class="mb-3 text-sm font-extrabold text-[#334155]">{{ $distribution['group'] }}</h3>@endif
                                            <div class="overflow-x-auto rounded-xl border border-[#e5ebf5]">
                                                <table class="w-full min-w-[38rem] text-left text-sm">
                                                    <thead class="bg-[#f5f8ff] text-xs uppercase tracking-[0.06em] text-[#71809d]"><tr><th class="px-4 py-3">Answer</th><th class="px-4 py-3 text-right">Count</th><th class="px-4 py-3 text-right">Share</th><th class="w-48 px-4 py-3">Distribution</th></tr></thead>
                                                    <tbody>@forelse ($distribution['options'] as $option)<tr class="border-t border-[#edf1f7]"><td class="px-4 py-3 font-bold text-[#334155]">{{ $option['label'] }}</td><td class="px-4 py-3 text-right font-extrabold text-[#0b1739]">{{ $option['count'] }}</td><td class="px-4 py-3 text-right font-bold text-[#52658b]">{{ $option['percentage'] === null ? 'N/A' : rtrim(rtrim(number_format($option['percentage'], 1), '0'), '.').'%' }}</td><td class="px-4 py-3"><div class="h-2 overflow-hidden rounded-full bg-[#e9eef7]"><div class="h-full rounded-full bg-[#1457ff]" style="width: {{ min(100, (float) ($option['percentage'] ?? 0)) }}%"></div></div></td></tr>@empty<tr><td colspan="4" class="px-4 py-8 text-center text-[#71809d]">No selectable answers are defined for this question.</td></tr>@endforelse</tbody>
                                                </table>
                                            </div>
                                            <p class="mt-2 text-xs leading-5 text-[#71809d]">Of {{ $distribution['answered'] }} responses answering this {{ $distribution['group'] ? 'group' : 'question' }}.</p>
                                        </section>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="admin-card px-5 py-14 text-center"><p class="text-base font-extrabold text-[#334155]">No matching selected answers</p><p class="mt-2 text-sm text-[#71809d]">Try clearing the section, question, or search filters.</p></div>
                    @endforelse
                </div>
            @else
                <section class="mt-5 grid gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="grid min-w-0 gap-4">
                        @forelse ($feedback as $answer)
                            <article class="admin-card p-5 sm:p-6">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><p class="text-sm font-semibold leading-6 text-[#334155]">{{ $answer->answer_value }}</p><span class="shrink-0 text-xs font-bold text-[#71809d]">{{ $answer->response?->created_at?->setTimezone($analytics['meta']['timezone'])->format('M j, Y') }}</span></div>
                                <form method="POST" action="{{ route('admin.feedback.review', $answer) }}" class="mt-4 border-t border-[#edf1f7] pt-4">
                                    @csrf
                                    @method('PUT')
                                    <fieldset><legend class="text-xs font-extrabold text-[#334155]">Administrator-reviewed themes</legend><div class="mt-3 flex flex-wrap gap-2">@foreach ($themes as $theme)@php($checked = $answer->themeAssignments->contains('feedback_theme_id', $theme->id))<label class="cursor-pointer"><input class="peer sr-only" type="checkbox" name="themes[]" value="{{ $theme->key }}" @checked($checked)><span class="inline-flex rounded-full border border-[#dce5f2] bg-white px-3 py-2 text-xs font-bold text-[#52658b] transition peer-checked:border-[#1457ff] peer-checked:bg-[#edf3ff] peer-checked:text-[#1457ff] peer-focus-visible:ring-2 peer-focus-visible:ring-[#1457ff]">{{ $theme->label }}</span></label>@endforeach</div></fieldset>
                                    <div class="mt-4 flex items-center justify-between gap-3"><p class="text-xs text-[#71809d]">Themes only affect reviewed-feedback analysis after you save.</p><button class="shrink-0 rounded-lg bg-[#1457ff] px-4 py-2 text-xs font-extrabold text-white hover:bg-[#0f46cf]">Save review</button></div>
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
    </div>
</x-app-layout>
