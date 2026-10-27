@php
    $formOptions = collect(old('options', $question->exists
        ? $question->options->map(fn ($option) => [
            'id' => $option->id,
            'label' => $option->label,
            'icon' => $option->icon,
            'tone' => $option->tone,
            'group_name' => $option->group_name,
        ])->values()->all()
        : []));
    $iconChoices = [
        'message-circle' => 'Conversation', 'users' => 'People', 'calendar' => 'Calendar',
        'calendar-days' => 'Calendar days', 'clock' => 'Clock', 'star' => 'Star',
        'thumbs-up' => 'Thumbs up', 'lightbulb' => 'Idea', 'microphone' => 'Microphone',
        'gamepad' => 'Game', 'target' => 'Target', 'help-circle' => 'Help',
        'info' => 'Information', 'check-circle' => 'Check', 'globe' => 'Globe',
        'book-open' => 'Book', 'sparkles' => 'Sparkles', 'heart' => 'Heart',
    ];
@endphp

<form method="POST" action="{{ $action }}" x-data="questionEditor(@js($formOptions), @js(old('type', $question->type)))" class="mt-7 grid gap-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            <p class="font-extrabold">Check the highlighted fields and try again.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="grid gap-5 rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] sm:p-7">
        <div class="grid gap-5 md:grid-cols-2">
            <label class="grid gap-2 text-sm font-bold text-[#10214b]">
                Section
                <select name="section" class="rounded-2xl border-[#cbd7e8] px-4 py-3 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]" required>
                    @foreach ($sections as $section)<option value="{{ $section }}" @selected(old('section', $question->section) === $section)>{{ $section }}</option>@endforeach
                </select>
            </label>
            <label class="grid gap-2 text-sm font-bold text-[#10214b]">
                Answer type
                <select name="type" x-model="type" class="rounded-2xl border-[#cbd7e8] px-4 py-3 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]" required>
                    @foreach ($types as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
            </label>
        </div>

        <label class="grid gap-2 text-sm font-bold text-[#10214b]">
            Question
            <textarea name="prompt" rows="3" maxlength="500" class="rounded-2xl border-[#cbd7e8] px-4 py-3 text-base font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]" required>{{ old('prompt', $question->prompt) }}</textarea>
        </label>

        <label class="grid gap-2 text-sm font-bold text-[#10214b]">
            Helper text
            <input name="help_text" value="{{ old('help_text', $question->help_text) }}" maxlength="255" class="rounded-2xl border-[#cbd7e8] px-4 py-3 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]" placeholder="For example: Choose one answer.">
        </label>

        <div class="grid gap-4 sm:grid-cols-3">
            <label class="flex items-center gap-3 rounded-2xl border border-[#dce5f2] p-4 text-sm font-bold">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $question->is_required)) class="rounded border-[#b9c9df] text-[#1357e8] focus:ring-[#1357e8]">
                Required answer
            </label>
            <label class="flex items-center gap-3 rounded-2xl border border-[#dce5f2] p-4 text-sm font-bold">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active ?? true)) class="rounded border-[#b9c9df] text-[#1357e8] focus:ring-[#1357e8]">
                Show on survey
            </label>
            <label x-show="type === 'multiple_choice'" class="grid gap-1 rounded-2xl border border-[#dce5f2] p-3 text-xs font-bold">
                Maximum choices
                <input type="number" name="max_selections" min="1" max="20" value="{{ old('max_selections', $question->max_selections ?? 3) }}" :disabled="type !== 'multiple_choice'" class="rounded-xl border-[#cbd7e8] px-3 py-2 text-sm focus:border-[#1357e8] focus:ring-[#1357e8]">
            </label>
        </div>
    </section>

    <section x-show="type !== 'long_text'" class="rounded-3xl border border-[#e1e8f3] bg-white p-5 shadow-[0_12px_32px_rgba(15,45,90,0.05)] sm:p-7">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-lg font-extrabold">Answer options</h2><p class="mt-1 text-sm text-[#63749a]">Add clear, distinct choices. Icons and colors keep the student form easy to scan.</p></div>
            <button type="button" @click="addOption" class="rounded-xl border border-[#b9c9df] px-4 py-2.5 text-xs font-bold text-[#1357e8] hover:bg-[#edf3ff]">Add option</button>
        </div>

        <div class="mt-5 grid gap-3">
            <template x-for="(option, index) in options" :key="option.clientKey">
                <div class="grid gap-3 rounded-2xl bg-[#f7f9fd] p-4 lg:grid-cols-[minmax(0,1fr)_10rem_8rem_auto] lg:items-end">
                    <input type="hidden" :name="`options[${index}][id]`" x-model="option.id" :disabled="type === 'long_text' || !option.id">
                    <label class="grid gap-1.5 text-xs font-bold text-[#42547a]">Option label<input :name="`options[${index}][label]`" x-model="option.label" :disabled="type === 'long_text'" maxlength="255" class="rounded-xl border-[#cbd7e8] px-3 py-2.5 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]" required></label>
                    <label class="grid gap-1.5 text-xs font-bold text-[#42547a]">Icon<select :name="`options[${index}][icon]`" x-model="option.icon" :disabled="type === 'long_text'" class="rounded-xl border-[#cbd7e8] px-3 py-2.5 text-xs font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]">@foreach ($iconChoices as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="grid gap-1.5 text-xs font-bold text-[#42547a]">Color<select :name="`options[${index}][tone]`" x-model="option.tone" :disabled="type === 'long_text'" class="rounded-xl border-[#cbd7e8] px-3 py-2.5 text-xs font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]"><option value="blue">Blue</option><option value="green">Green</option><option value="orange">Orange</option><option value="purple">Purple</option><option value="red">Red</option><option value="neutral">Gray</option></select></label>
                    <button type="button" @click="removeOption(index)" :disabled="options.length <= 2" class="rounded-xl border border-rose-200 px-3 py-2.5 text-xs font-bold text-rose-600 disabled:cursor-not-allowed disabled:opacity-40">Remove</button>
                    <label x-show="type === 'grouped_choice'" class="grid gap-1.5 text-xs font-bold text-[#42547a] lg:col-span-4">Group name<input :name="`options[${index}][group_name]`" x-model="option.group_name" :disabled="type !== 'grouped_choice'" class="rounded-xl border-[#cbd7e8] px-3 py-2.5 text-sm font-semibold focus:border-[#1357e8] focus:ring-[#1357e8]" placeholder="For example: Preferred time"></label>
                </div>
            </template>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.questions.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#cbd7e8] bg-white px-5 py-3 text-sm font-bold text-[#10214b]">Cancel</a>
        <button class="inline-flex items-center justify-center rounded-2xl bg-[#1357e8] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:bg-[#0b43be]">{{ $submitLabel }}</button>
    </div>
</form>
