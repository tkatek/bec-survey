@props(['alertData'])

<div
    x-data="{ open: false }"
    @keydown.escape.window="if (open) { open = false; $nextTick(() => $refs.trigger.focus()) }"
    class="relative"
>
    <button
        x-ref="trigger"
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        aria-haspopup="dialog"
        aria-label="Open feedback alerts"
        class="relative inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#dce5f2] bg-white text-[#334a75] shadow-sm transition hover:border-[#b8c9e7] hover:text-[#1457ff] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1457ff] focus-visible:ring-offset-2"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg>
        @if ($alertData['unread'] > 0)
            <span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-[#ef476f] px-1 text-[10px] font-extrabold text-white">{{ $alertData['unread'] }}</span>
        @endif
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        @click.outside="open = false"
        role="dialog"
        aria-label="Feedback alerts"
        class="fixed inset-x-3 top-20 z-50 max-h-[calc(100dvh-6rem)] overflow-y-auto rounded-2xl border border-[#dce5f2] bg-white p-4 shadow-[0_24px_70px_rgba(11,23,57,0.22)] sm:absolute sm:inset-x-auto sm:right-0 sm:top-14 sm:w-[25rem]"
    >
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-[#0b1739]">Notifications</h2>
                <p class="mt-1 text-xs leading-5 text-[#64748b]">{{ $alertData['unread'] }} unread alerts · monitoring {{ $alertData['monitoringLabel'] }}</p>
            </div>
            @if ($alertData['unread'] > 0)
                <form method="POST" action="{{ route('admin.alerts.read-all') }}">
                    @csrf
                    @method('PUT')
                    <button class="text-xs font-bold text-[#1457ff] hover:underline">Mark all read</button>
                </form>
            @endif
        </div>

        <div class="mt-4 grid gap-3">
            @forelse ($alertData['alerts'] as $alert)
                <article @class(['rounded-xl border p-3', 'border-[#cbdaf6] bg-[#f5f8ff]' => ! $alert['isRead'], 'border-[#e5ebf5] bg-white' => $alert['isRead']])>
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-extrabold uppercase text-amber-700">{{ $alert['severity'] }}</span>
                        <span class="text-[10px] font-semibold text-[#71809d]">{{ $alert['timestamp'] }}</span>
                    </div>
                    <h3 class="mt-2 text-sm font-extrabold leading-5 text-[#0b1739]">{{ $alert['title'] }}</h3>
                    <p class="mt-1 text-xs leading-5 text-[#52658b]">{{ $alert['matchingCount'] }} responses · threshold {{ $alert['threshold'] }} · {{ $alert['period'] }}</p>
                    <p class="mt-1 text-xs leading-5 text-[#64748b]">{{ $alert['explanation'] }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <a href="{{ $alert['evidenceUrl'] }}" class="rounded-lg bg-[#1457ff] px-3 py-2 text-xs font-bold text-white hover:bg-[#0f46cf]">View evidence</a>
                        @if (! $alert['isRead'])
                            <form method="POST" action="{{ $alert['readUrl'] }}">
                                @csrf
                                @method('PUT')
                                <button class="rounded-lg border border-[#dce5f2] px-3 py-2 text-xs font-bold text-[#334a75] hover:border-[#1457ff]">Mark as read</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ $alert['resolveUrl'] }}">
                            @csrf
                            @method('PUT')
                            <button class="rounded-lg border border-[#dce5f2] px-3 py-2 text-xs font-bold text-[#334a75] hover:border-[#1457ff]">Resolve</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-[#cbd5e1] bg-[#f8fafc] px-4 py-8 text-center">
                    <svg class="mx-auto h-7 w-7 text-[#91a1bb]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7 9 18l-5-5" /></svg>
                    <p class="mt-2 text-sm font-bold text-[#334155]">No notifications</p>
                    <p class="mt-1 text-xs text-[#64748b]">No feedback rule is currently above its threshold.</p>
                </div>
            @endforelse
        </div>

        @if (count($alertData['disabledRules']) > 0)
            <div class="mt-4 border-t border-[#e5ebf5] pt-4">
                <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#71809d]">Disabled rules</p>
                <div class="mt-2 grid gap-2">
                    @foreach ($alertData['disabledRules'] as $rule)
                        <div class="rounded-lg bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900"><strong>{{ $rule['ruleKey'] }}:</strong> {{ $rule['reason'] }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
