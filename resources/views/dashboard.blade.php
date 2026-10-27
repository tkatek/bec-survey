<x-app-layout>
    @php
        $toneColors = [
            'blue' => '#4386f4',
            'green' => '#24c98b',
            'orange' => '#ffb43c',
            'purple' => '#9a67e8',
            'red' => '#ff6578',
            'neutral' => '#8fa2bf',
        ];
        $attendanceSegments = [];
        $segmentStart = 0;
        foreach ($charts['attendance'] as $item) {
            $segmentEnd = $segmentStart + $item['percentage'];
            $attendanceSegments[] = ($toneColors[$item['tone']] ?? $toneColors['blue'])." {$segmentStart}% {$segmentEnd}%";
            $segmentStart = $segmentEnd;
        }
        $donutStyle = $attendanceSegments === [] || $periodResponses === 0
            ? 'background: conic-gradient(#e8eef7 0 100%)'
            : 'background: conic-gradient('.implode(', ', $attendanceSegments).')';
    @endphp

    <div class="mx-auto w-full max-w-[1600px] p-4 sm:p-6 lg:p-8">
        <header class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-bold text-[#1357e8]">Survey analytics</p>
                <h1 class="mt-1 max-w-4xl text-2xl font-extrabold tracking-[-0.035em] text-[#071326] sm:text-3xl lg:text-4xl">Conversation Room Feedback Survey</h1>
                <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-[#63749a] sm:text-base">See where students are thriving, where they struggle, and what would bring them back more often.</p>
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="w-full sm:w-auto">
                <label for="period" class="sr-only">Analytics period</label>
                <select id="period" name="period" onchange="this.form.submit()" class="w-full rounded-2xl border-[#dce5f2] bg-white px-5 py-3 text-sm font-bold text-[#10214b] shadow-sm focus:border-[#1357e8] focus:ring-[#1357e8] sm:min-w-52">
                    <option value="7" @selected($period === 7)>Last 7 days</option>
                    <option value="30" @selected($period === 30)>Last 30 days</option>
                    <option value="90" @selected($period === 90)>Last 90 days</option>
                </select>
            </form>
        </header>

        <section class="mt-7 grid gap-4 sm:grid-cols-2 2xl:grid-cols-4" aria-label="Survey overview">
            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.06)]">
                <div class="flex items-start justify-between gap-4">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e8f1ff] text-[#1357e8]"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg></span>
                    <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-600' => $responseTrend >= 0, 'bg-rose-50 text-rose-600' => $responseTrend < 0])>{{ $responseTrend >= 0 ? '+' : '' }}{{ $responseTrend }}%</span>
                </div>
                <p class="mt-4 text-sm font-semibold text-[#63749a]">Responses in this period</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight">{{ number_format($periodResponses) }}</p>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.06)]">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 8h8M8 12h8M8 16h5" /></svg></span>
                <p class="mt-4 text-sm font-semibold text-[#63749a]">Live questions</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight">{{ $activeQuestions }}</p>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.06)]">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-50 text-purple-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" /></svg></span>
                <p class="mt-4 text-sm font-semibold text-[#63749a]">Positive activity rating</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight">{{ $positiveUsefulnessPercentage }}<span class="text-lg text-[#63749a]">%</span></p>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.06)]">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 14 4-4 4 4 8-8" /><path d="M14 6h6v6" /></svg></span>
                <p class="mt-4 text-sm font-semibold text-[#63749a]">Likely to attend again</p>
                <p class="mt-1 text-3xl font-extrabold tracking-tight">{{ $likelyToAttendPercentage }}<span class="text-lg text-[#63749a]">%</span></p>
            </article>
        </section>

        @if ($periodResponses === 0)
            <div class="mt-6 flex flex-col gap-4 rounded-3xl border border-dashed border-[#b9c9df] bg-white/70 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-extrabold">Your analytics are ready</h2>
                    <p class="mt-1 text-sm leading-6 text-[#63749a]">Charts will populate as soon as students complete the live survey.</p>
                </div>
                <a href="{{ route('conversation-room.create') }}" target="_blank" class="inline-flex items-center justify-center rounded-2xl bg-[#1357e8] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-[#0b43be]">Open survey</a>
            </div>
        @endif

        <section class="mt-6 grid gap-5 xl:grid-cols-12">
            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] xl:col-span-7">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold">Response momentum</h2>
                        <p class="mt-1 text-sm text-[#63749a]">Daily completed surveys</p>
                    </div>
                    <span class="rounded-full bg-[#edf3ff] px-3 py-1 text-xs font-bold text-[#1357e8]">{{ $period }} days</span>
                </div>
                <div class="mt-7 flex h-44 items-end gap-2 border-b border-[#dce5f2] px-1 sm:gap-3">
                    @foreach ($responseSeries as $point)
                        <div class="group flex min-w-0 flex-1 flex-col items-center justify-end gap-2" title="{{ $point['label'] }}: {{ $point['value'] }} responses">
                            <span class="text-[10px] font-bold text-[#63749a] opacity-0 transition group-hover:opacity-100">{{ $point['value'] }}</span>
                            <span class="w-full max-w-7 rounded-t-lg bg-gradient-to-t from-[#1357e8] to-[#62a2ff]" style="height: {{ max(4, (int) round(($point['value'] / $maxSeriesValue) * 120)) }}px"></span>
                            <span class="hidden text-[9px] font-semibold text-[#63749a] 2xl:block">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] xl:col-span-5">
                <h2 class="text-lg font-extrabold">Attendance frequency</h2>
                <p class="mt-1 text-sm text-[#63749a]">How often students join the room</p>
                <div class="mt-6 grid gap-6 sm:grid-cols-[10rem_1fr] sm:items-center">
                    <div class="relative mx-auto h-40 w-40 rounded-full" style="{{ $donutStyle }}">
                        <div class="absolute inset-7 grid place-items-center rounded-full bg-white text-center">
                            <span class="text-2xl font-extrabold">{{ $periodResponses }}</span>
                            <span class="text-[10px] font-semibold text-[#63749a]">responses</span>
                        </div>
                    </div>
                    <div class="grid gap-2.5">
                        @foreach ($charts['attendance'] as $item)
                            <div class="grid grid-cols-[auto_1fr_auto] items-center gap-2 text-xs">
                                <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}"></span>
                                <span class="truncate font-semibold text-[#63749a]">{{ $item['label'] }}</span>
                                <strong>{{ $item['percentage'] }}%</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] xl:col-span-6">
                <h2 class="text-lg font-extrabold">Attendance change</h2>
                <p class="mt-1 text-sm text-[#63749a]">How participation changed after the update</p>
                <div class="mt-6 grid gap-4">
                    @foreach ($charts['attendanceChange'] as $item)
                        <div>
                            <div class="flex items-end justify-between gap-4 text-xs"><span class="font-semibold text-[#42547a]">{{ $item['label'] }}</span><strong>{{ $item['percentage'] }}%</strong></div>
                            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-[#edf1f7]"><span class="block h-full rounded-full" style="width: {{ $item['percentage'] }}%; background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}"></span></div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] xl:col-span-6">
                <h2 class="text-lg font-extrabold">Top attendance barriers</h2>
                <p class="mt-1 text-sm text-[#63749a]">The most common reasons students attend less often</p>
                <div class="mt-6 grid gap-4">
                    @foreach ($charts['barriers'] as $item)
                        <div class="grid grid-cols-[minmax(0,1fr)_4rem] items-center gap-4">
                            <div><p class="truncate text-xs font-semibold text-[#42547a]">{{ $item['label'] }}</p><div class="mt-2 h-2 overflow-hidden rounded-full bg-[#edf1f7]"><span class="block h-full rounded-full bg-[#4386f4]" style="width: {{ $item['percentage'] }}%"></span></div></div>
                            <strong class="text-right text-sm">{{ $item['percentage'] }}%</strong>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] xl:col-span-4">
                <h2 class="text-lg font-extrabold">Preferred format</h2>
                <div class="mt-5 grid gap-3">
                    @foreach ($charts['format'] as $item)
                        <div class="flex items-center gap-3 rounded-2xl bg-[#f7f9fd] p-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl text-xs font-extrabold text-white" style="background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}">{{ $item['percentage'] }}%</span>
                            <span class="text-xs font-bold leading-5 text-[#42547a]">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] xl:col-span-4">
                <h2 class="text-lg font-extrabold">Preferred time</h2>
                <div class="mt-6 flex h-56 items-end gap-3 border-b border-[#dce5f2] px-1">
                    @foreach ($charts['preferredTime'] as $item)
                        <div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-2 text-center">
                            <strong class="text-xs">{{ $item['percentage'] }}%</strong>
                            <span class="w-full max-w-12 rounded-t-xl" style="height: {{ max(6, $item['percentage'] * 3) }}px; background-color: {{ $toneColors[$item['tone']] ?? $toneColors['blue'] }}"></span>
                            <span class="h-9 overflow-hidden text-[9px] font-semibold leading-3 text-[#63749a]">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-3xl border border-[#e1e8f3] bg-[#071326] p-5 text-white shadow-[0_12px_32px_rgba(15,45,90,0.12)] xl:col-span-4">
                <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-extrabold">Student voices</h2><span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold text-blue-100">Latest</span></div>
                <div class="mt-5 grid gap-3">
                    @forelse ($latestFeedback as $feedback)
                        <blockquote class="rounded-2xl bg-white/10 p-4 text-xs font-medium leading-5 text-blue-50">“{{ $feedback->answer_value }}”</blockquote>
                    @empty
                        <div class="rounded-2xl border border-dashed border-white/20 p-5 text-sm leading-6 text-blue-100">Written feedback will appear here when students share suggestions.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-app-layout>
