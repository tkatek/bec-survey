<x-app-layout>
    <div class="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold text-[#1357e8]">Survey builder</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-[-0.03em] sm:text-3xl">Manage questions</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#63749a]">Every active question appears on the student survey in this order. Historical answers stay safe when a question is removed.</p>
            </div>
            <a href="{{ route('admin.questions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#1357e8] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-[#0b43be] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1357e8] focus-visible:ring-offset-2">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14" /></svg>
                Add question
            </a>
        </header>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">{{ session('error') }}</div>
        @endif

        <div class="mt-7 grid gap-4">
            @forelse ($questions as $question)
                <article class="grid gap-4 rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] lg:grid-cols-[auto_minmax(0,1fr)_auto] lg:items-center">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl bg-[#071326] text-sm font-extrabold text-white">{{ str_pad((string) $question->sort_order, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#edf3ff] px-3 py-1 text-[10px] font-bold text-[#1357e8]">{{ $question->section }}</span>
                            <span @class(['rounded-full px-3 py-1 text-[10px] font-bold', 'bg-emerald-50 text-emerald-600' => $question->is_active, 'bg-slate-100 text-slate-500' => ! $question->is_active])>{{ $question->is_active ? 'Live' : 'Hidden' }}</span>
                            <span class="text-[10px] font-bold text-[#63749a]">{{ \App\Models\SurveyQuestion::typeLabels()[$question->type] ?? $question->type }}</span>
                        </div>
                        <h2 class="mt-3 text-base font-extrabold leading-6 text-[#071326] sm:text-lg">{{ $question->prompt }}</h2>
                        <p class="mt-2 text-xs font-semibold text-[#63749a]">{{ $question->options->count() }} options · {{ number_format($question->answers_count) }} recorded answers</p>
                    </div>
                    <div class="flex items-center gap-2 lg:justify-end">
                        <a href="{{ route('admin.questions.edit', $question) }}" class="inline-flex flex-1 items-center justify-center rounded-xl border border-[#cbd7e8] px-4 py-2.5 text-xs font-bold text-[#10214b] transition hover:border-[#1357e8] hover:text-[#1357e8] lg:flex-none">Edit</a>
                        <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" onsubmit="return confirm('Remove this question from the live survey? Existing answers will be preserved.')">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-xs font-bold text-rose-600 transition hover:bg-rose-50">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-[#b9c9df] bg-white p-10 text-center">
                    <h2 class="text-lg font-extrabold">No survey questions yet</h2>
                    <p class="mt-2 text-sm text-[#63749a]">Add the first question to publish the student survey.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
