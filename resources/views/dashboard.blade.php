<x-app-layout>
    @php
        $toneColors = [
            'blue' => '#4386f4',
            'green' => '#20c997',
            'orange' => '#ff9f2f',
            'purple' => '#9264e8',
            'red' => '#f45b73',
            'neutral' => '#91a1bb',
        ];
        $attendanceSegments = [];
        $segmentStart = 0;
        foreach ($charts['attendance'] as $item) {
            $segmentEnd = min(100, $segmentStart + $item['percentage']);
            $attendanceSegments[] = ($toneColors[$item['tone']] ?? $toneColors['blue'])." {$segmentStart}% {$segmentEnd}%";
            $segmentStart = $segmentEnd;
        }
        if ($segmentStart < 100) {
            $attendanceSegments[] = "#e7edf7 {$segmentStart}% 100%";
        }
        $donutStyle = $periodResponses === 0
            ? 'background: conic-gradient(#e7edf7 0 100%)'
            : 'background: conic-gradient('.implode(', ', $attendanceSegments).')';
    @endphp

    <div x-data="dashboardAnalytics" class="relative min-h-screen min-w-0 overflow-x-hidden bg-[radial-gradient(circle_at_8%_0%,_#d5e8ff_0,_#edf4ff_32%,_#e9effb_70%,_#f5f8ff_100%)]">
        <div class="pointer-events-none absolute -left-32 top-24 h-96 w-96 rounded-full bg-[#1357e8]/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute right-0 top-0 h-[30rem] w-[30rem] rounded-full bg-[#7faeff]/15 blur-3xl" aria-hidden="true"></div>
        <div class="relative mx-auto w-full max-w-[1680px] p-4 sm:p-6 lg:p-8 2xl:p-10">
            <header class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-[#1357e8]">Survey analytics</p>
                    <h1 class="mt-1 max-w-4xl text-2xl font-extrabold tracking-[-0.04em] text-[#071326] sm:text-3xl lg:text-[2.45rem] lg:leading-tight">Conversation Room Feedback Survey</h1>
                    <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-[#586b91] sm:text-base">See where students are thriving, where they struggle, and what would bring them back more often.</p>
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="w-full shrink-0 sm:w-auto">
                    <label for="period" class="sr-only">Analytics period</label>
                    <select id="period" name="period" onchange="this.form.submit()" class="w-full rounded-2xl border border-white/80 bg-white/85 px-5 py-3.5 text-sm font-bold text-[#10214b] shadow-[0_12px_30px_rgba(34,74,135,0.10)] backdrop-blur focus:border-[#1357e8] focus:ring-[#1357e8] sm:min-w-56">
                        <option value="7" @selected($period === 7)>Last 7 days</option>
                        <option value="30" @selected($period === 30)>Last 30 days</option>
                        <option value="90" @selected($period === 90)>Last 90 days</option>
                    </select>
                </form>
            </header>

            <section class="mt-7 grid min-w-0 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Survey overview">
                <article data-dashboard-stat class="min-w-0 overflow-hidden rounded-[1.6rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#e9f2ff] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.10)]">
                    <div class="flex items-start justify-between gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-[#e4efff] text-[#1357e8]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg></span>
                        <div class="flex flex-wrap justify-end gap-2">
                            @if ($demoResponses > 0)
                                <span class="rounded-full bg-[#071326] px-2.5 py-1 text-[10px] font-bold text-white">{{ $demoResponses }} demo</span>
                            @endif
                            <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-600' => $responseTrend >= 0, 'bg-rose-50 text-rose-600' => $responseTrend < 0])>{{ $responseTrend >= 0 ? '+' : '' }}{{ $responseTrend }}%</span>
                        </div>
                    </div>
                    <p class="mt-5 text-sm font-semibold text-[#607296]">Responses in this period</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-[#071326]"><span data-count-up="{{ $periodResponses }}" aria-label="{{ $periodResponses }} responses">0</span></p>
                </article>

                <article data-dashboard-stat class="min-w-0 overflow-hidden rounded-[1.6rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#e8fbf5] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.10)]">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 8h8M8 12h8M8 16h5" /></svg></span>
                    <p class="mt-5 text-sm font-semibold text-[#607296]">Live questions</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-[#071326]"><span data-count-up="{{ $activeQuestions }}" aria-label="{{ $activeQuestions }} live questions">0</span></p>
                </article>

                <article data-dashboard-stat class="min-w-0 overflow-hidden rounded-[1.6rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#f4ebff] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.10)]">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-50 text-purple-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" /></svg></span>
                    <p class="mt-5 text-sm font-semibold text-[#607296]">Positive activity rating</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-[#071326]"><span data-count-up="{{ $positiveUsefulnessPercentage }}" data-suffix="%" aria-label="{{ $positiveUsefulnessPercentage }} percent positive">0%</span></p>
                </article>

                <article data-dashboard-stat class="min-w-0 overflow-hidden rounded-[1.6rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#fff3dd] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.10)]">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 14 4-4 4 4 8-8" /><path d="M14 6h6v6" /></svg></span>
                    <p class="mt-5 text-sm font-semibold text-[#607296]">Likely to attend again</p>
                    <p class="mt-1 text-3xl font-extrabold tracking-tight text-[#071326]"><span data-count-up="{{ $likelyToAttendPercentage }}" data-suffix="%" aria-label="{{ $likelyToAttendPercentage }} percent likely to attend">0%</span></p>
                </article>
            </section>

            @if ($periodResponses === 0)
                <div class="mt-6 flex flex-col gap-4 rounded-[1.6rem] border border-dashed border-[#9eb5d7] bg-white/70 p-6 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold">Your analytics are ready</h2>
                        <p class="mt-1 text-sm leading-6 text-[#607296]">Charts will populate as soon as students complete the live survey.</p>
                    </div>
                    <a href="{{ route('conversation-room.create') }}" target="_blank" class="inline-flex items-center justify-center rounded-2xl bg-[#1357e8] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-[#0b43be]">Open survey</a>
                </div>
            @else
                <section class="mt-5 grid min-w-0 gap-4 lg:grid-cols-2" aria-label="Key survey signals">
                    <article class="min-w-0 rounded-[1.4rem] border border-emerald-100 bg-emerald-50/80 p-4 sm:flex sm:items-center sm:gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-emerald-500 text-white"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 12 4 4L19 6" /></svg></span>
                        <div class="mt-3 min-w-0 sm:mt-0">
                            <p class="text-xs font-bold text-emerald-700">Strongest preference</p>
                            <p class="mt-1 truncate text-sm font-extrabold text-[#071326]">{{ $strongestFormat['label'] ?? 'More responses needed' }} <span class="text-emerald-600">{{ $strongestFormat['percentage'] ?? 0 }}%</span></p>
                        </div>
                    </article>
                    <article class="min-w-0 rounded-[1.4rem] border border-amber-100 bg-amber-50/80 p-4 sm:flex sm:items-center sm:gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-amber-500 text-white"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 9v4m0 4h.01" /><path d="M10.3 3.7 2.4 17.4A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.6L13.7 3.7a2 2 0 0 0-3.4 0Z" /></svg></span>
                        <div class="mt-3 min-w-0 sm:mt-0">
                            <p class="text-xs font-bold text-amber-700">Main attendance barrier</p>
                            <p class="mt-1 truncate text-sm font-extrabold text-[#071326]">{{ $topBarrier['label'] ?? 'More responses needed' }} <span class="text-amber-600">{{ $topBarrier['percentage'] ?? 0 }}%</span></p>
                        </div>
                    </article>
                </section>
            @endif

            <section class="mt-5 grid min-w-0 gap-5 xl:grid-cols-12">
                <article class="relative min-w-0 overflow-hidden rounded-[1.8rem] bg-[#071326] p-5 text-white shadow-[0_24px_60px_rgba(7,19,38,0.22)] sm:p-6 xl:col-span-8">
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_75%_20%,_rgba(255,139,31,0.16),_transparent_38%)]" aria-hidden="true"></div>
                    <div class="relative flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold sm:text-xl">Response momentum</h2>
                            <p class="mt-1 text-sm text-blue-100/70">Daily completed surveys across the latest two weeks</p>
                        </div>
                        <span class="w-fit rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-xs font-bold text-blue-50">{{ $period }} day view</span>
                    </div>

                    <div class="relative mt-5 min-w-0 overflow-hidden rounded-2xl border border-white/5 bg-[#09182d]/80 p-2 sm:p-4">
                        <svg class="block h-auto w-full max-w-full" viewBox="0 0 760 245" role="img" aria-label="Area chart showing completed survey responses by day">
                            <defs>
                                <linearGradient id="momentum-area" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#ff8a1f" stop-opacity="0.78" />
                                    <stop offset="58%" stop-color="#f56c1b" stop-opacity="0.28" />
                                    <stop offset="100%" stop-color="#ff8a1f" stop-opacity="0" />
                                </linearGradient>
                                <linearGradient id="momentum-line" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#ffc46b" />
                                    <stop offset="50%" stop-color="#ff8a1f" />
                                    <stop offset="100%" stop-color="#ffb451" />
                                </linearGradient>
                                <filter id="momentum-glow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feGaussianBlur stdDeviation="4" result="blur" />
                                    <feMerge><feMergeNode in="blur" /><feMergeNode in="SourceGraphic" /></feMerge>
                                </filter>
                                <clipPath id="momentum-reveal"><rect data-dashboard-chart-reveal x="0" y="0" width="760" height="245" /></clipPath>
                            </defs>

                            @foreach ([55, 105, 155, 205] as $gridLine)
                                <line x1="24" y1="{{ $gridLine }}" x2="736" y2="{{ $gridLine }}" stroke="rgba(157,183,220,0.12)" stroke-width="1" />
                            @endforeach

                            <g clip-path="url(#momentum-reveal)">
                                <path d="{{ $momentumChart['area'] }}" fill="url(#momentum-area)" />
                                <path d="{{ $momentumChart['line'] }}" fill="none" stroke="url(#momentum-line)" stroke-width="3" stroke-linecap="round" filter="url(#momentum-glow)" />
                                @foreach ($momentumChart['points'] as $point)
                                    <g>
                                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#ff9a37" stroke="#071326" stroke-width="2"><title>{{ $point['label'] }}: {{ $point['value'] }} responses</title></circle>
                                        @if ($point['value'] > 0)
                                            <text x="{{ $point['x'] }}" y="{{ max(18, $point['y'] - 12) }}" text-anchor="middle" fill="#ffd19a" font-size="10" font-weight="700">{{ $point['value'] }}</text>
                                        @endif
                                    </g>
                                @endforeach
                            </g>

                            @foreach ($momentumChart['points'] as $index => $point)
                                @if ($index % 2 === 0 || $loop->last)
                                    <text x="{{ $point['x'] }}" y="232" text-anchor="middle" fill="#7890b5" font-size="9" font-weight="600">{{ $point['label'] }}</text>
                                @endif
                            @endforeach
                        </svg>
                    </div>

                    <div class="relative mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-blue-100/70">
                        <span>Peak day: <strong class="text-white">{{ $responseSeries->sortByDesc('value')->first()['label'] }}</strong></span>
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-[#ff8a1f]"></span>Completed responses</span>
                    </div>
                </article>

                <article class="min-w-0 overflow-hidden rounded-[1.8rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#eaf3ff] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.10)] sm:p-6 xl:col-span-4">
                    <h2 class="text-lg font-extrabold text-[#071326]">Attendance frequency</h2>
                    <p class="mt-1 text-sm text-[#607296]">How often students join the room</p>
                    <div class="mt-6 grid min-w-0 gap-6 sm:grid-cols-[9.5rem_minmax(0,1fr)] sm:items-center xl:grid-cols-1 2xl:grid-cols-[9.5rem_minmax(0,1fr)]">
                        <div data-dashboard-donut class="relative mx-auto h-40 w-40 shrink-0 rounded-full shadow-[0_16px_36px_rgba(19,87,232,0.16)]" style="{{ $donutStyle }}">
                            <div class="absolute inset-7 grid place-items-center rounded-full bg-white text-center shadow-inner">
                                <span class="text-2xl font-extrabold text-[#071326]" data-count-up="{{ $periodResponses }}">0</span>
                                <span class="text-[10px] font-semibold text-[#607296]">responses</span>
                            </div>
                        </div>
                        <div class="grid min-w-0 gap-3">
                            @foreach ($charts['attendance'] as $item)
                                <div class="grid min-w-0 grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2 text-xs">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}"></span>
                                    <span class="min-w-0 leading-4 text-[#53668d]">{{ $item['label'] }}</span>
                                    <strong class="text-[#071326]">{{ $item['percentage'] }}%</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <article class="min-w-0 overflow-hidden rounded-[1.8rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#edf9f5] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.09)] sm:p-6 xl:col-span-6">
                    <h2 class="text-lg font-extrabold text-[#071326]">Attendance change</h2>
                    <p class="mt-1 text-sm text-[#607296]">How participation changed after the update</p>
                    <div class="mt-6 grid gap-4">
                        @foreach ($charts['attendanceChange'] as $item)
                            <div class="min-w-0">
                                <div class="flex min-w-0 items-end justify-between gap-4 text-xs"><span class="min-w-0 leading-4 text-[#42547a]">{{ $item['label'] }}</span><strong class="shrink-0 text-[#071326]">{{ $item['percentage'] }}%</strong></div>
                                <div class="mt-2.5 h-2.5 overflow-hidden rounded-full bg-[#e8eef7]"><span data-dashboard-bar class="block h-full rounded-full" style="width: {{ min(100, $item['percentage']) }}%; background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}"></span></div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="min-w-0 overflow-hidden rounded-[1.8rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#fff6e7] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.09)] sm:p-6 xl:col-span-6">
                    <h2 class="text-lg font-extrabold text-[#071326]">Top attendance barriers</h2>
                    <p class="mt-1 text-sm text-[#607296]">The most common reasons students attend less often</p>
                    <div class="mt-6 grid gap-4">
                        @foreach ($charts['barriers'] as $item)
                            <div class="grid min-w-0 grid-cols-[minmax(0,1fr)_3rem] items-center gap-3">
                                <div class="min-w-0"><p class="text-xs font-semibold leading-4 text-[#42547a]">{{ $item['label'] }}</p><div class="mt-2 h-2 overflow-hidden rounded-full bg-[#e8eef7]"><span data-dashboard-bar class="block h-full rounded-full bg-gradient-to-r from-[#1357e8] to-[#6aa7ff]" style="width: {{ min(100, $item['percentage']) }}%"></span></div></div>
                                <strong class="text-right text-sm text-[#071326]">{{ $item['percentage'] }}%</strong>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="min-w-0 overflow-hidden rounded-[1.8rem] border border-white/80 bg-white/90 p-5 shadow-[0_18px_45px_rgba(31,72,132,0.09)] backdrop-blur sm:p-6 xl:col-span-4">
                    <h2 class="text-lg font-extrabold text-[#071326]">Preferred format</h2>
                    <p class="mt-1 text-sm text-[#607296]">How learners want to practise</p>
                    <div class="mt-5 grid min-w-0 gap-3">
                        @foreach ($charts['format'] as $item)
                            <div class="flex min-w-0 items-center gap-3 rounded-2xl bg-[#f4f7fc] p-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-[11px] font-extrabold text-white" style="background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}">{{ $item['percentage'] }}%</span>
                                <span class="min-w-0 text-xs font-bold leading-5 text-[#42547a]">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="min-w-0 overflow-hidden rounded-[1.8rem] border border-white/80 bg-gradient-to-br from-white via-white to-[#f2ecff] p-5 shadow-[0_18px_45px_rgba(31,72,132,0.09)] sm:p-6 xl:col-span-4">
                    <h2 class="text-lg font-extrabold text-[#071326]">Preferred time</h2>
                    <p class="mt-1 text-sm text-[#607296]">When students want to join</p>
                    <div class="mt-6 grid h-56 min-w-0 grid-cols-5 items-end gap-2 overflow-hidden border-b border-[#dce5f2] sm:gap-3">
                        @foreach ($charts['preferredTime'] as $item)
                            <div class="flex min-w-0 flex-col items-center justify-end gap-2 text-center">
                                <strong class="text-[11px] text-[#071326]">{{ $item['percentage'] }}%</strong>
                                <span data-dashboard-column class="w-full max-w-12 rounded-t-xl" style="height: {{ max(8, min(100, $item['percentage']) * 1.15) }}px; background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}"></span>
                                <span class="h-12 w-full overflow-hidden text-[8px] font-semibold leading-3 text-[#607296] sm:text-[9px]">{{ $item['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="min-w-0 overflow-hidden rounded-[1.8rem] bg-[#071326] p-5 text-white shadow-[0_24px_55px_rgba(7,19,38,0.20)] sm:p-6 xl:col-span-4">
                    <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-extrabold">Student voices</h2><span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold text-blue-100">Latest</span></div>
                    <p class="mt-1 text-sm text-blue-100/65">What learners would change next</p>
                    <div class="mt-5 grid gap-3">
                        @forelse ($latestFeedback as $feedback)
                            <blockquote class="rounded-2xl border border-white/10 bg-white/10 p-4 text-xs font-medium leading-5 text-blue-50">“{{ $feedback->answer_value }}”</blockquote>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/20 p-5 text-sm leading-6 text-blue-100">Written feedback will appear here when students share suggestions.</div>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </div>
</x-app-layout>
