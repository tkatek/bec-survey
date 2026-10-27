<?php

namespace App;

use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveySection;
use App\Models\SurveyVersion;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SurveyDraftManager
{
    public function activeVersion(): SurveyVersion
    {
        return SurveyVersion::query()->active()->sole();
    }

    public function editingVersion(): SurveyVersion
    {
        return SurveyVersion::query()->draft()->first() ?? $this->activeVersion();
    }

    /**
     * @return array{
     *     questions: Collection<int, SurveyQuestion>,
     *     sections: Collection<int, string>,
     *     surveyConfiguration: array<string, mixed>,
     *     surveyVersion: SurveyVersion,
     *     isPreview: bool
     * }
     */
    public function surveyViewData(SurveyVersion $version, bool $isPreview = false): array
    {
        $surveySections = $version->sections()
            ->where('is_active', true)
            ->whereHas('questions', fn ($query) => $query->where('is_active', true))
            ->with([
                'questions' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with(['options' => fn ($optionQuery) => $optionQuery->where('is_active', true)])
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $questions = $surveySections
            ->flatMap(fn (SurveySection $section) => $section->questions)
            ->values();
        $sections = $surveySections->pluck('title')->values();
        $surveyConfiguration = [
            'totalSteps' => $questions->count(),
            'isPreview' => $isPreview,
            'questions' => $questions->map(fn (SurveyQuestion $question): array => [
                'id' => $question->id,
                'required' => $question->is_required,
                'type' => $question->type,
                'maxSelections' => $question->max_selections,
                'groups' => $question->options->pluck('group_name')->filter()->unique()->values()->all(),
            ])->all(),
            'sections' => $sections->map(function (string $section) use ($questions): array {
                $indexes = $questions
                    ->map(fn (SurveyQuestion $question, int $index): ?int => $question->section === $section ? $index : null)
                    ->filter(fn (?int $index): bool => $index !== null)
                    ->values();

                return [
                    'name' => $section,
                    'start' => $indexes->first(),
                    'end' => $indexes->last(),
                ];
            })->all(),
        ];

        return compact('questions', 'sections', 'surveyConfiguration') + [
            'surveyVersion' => $version,
            'isPreview' => $isPreview,
        ];
    }

    public function draft(User $user): SurveyVersion
    {
        return DB::transaction(function () use ($user): SurveyVersion {
            $versions = SurveyVersion::query()
                ->whereIn('status', [SurveyVersion::STATUS_ACTIVE, SurveyVersion::STATUS_DRAFT])
                ->lockForUpdate()
                ->get();
            $existingDraft = $versions->firstWhere('status', SurveyVersion::STATUS_DRAFT);

            if ($existingDraft) {
                return $existingDraft;
            }

            $activeVersion = $versions->firstWhere('status', SurveyVersion::STATUS_ACTIVE);

            if (! $activeVersion) {
                throw ValidationException::withMessages([
                    'survey' => 'The active survey could not be found.',
                ]);
            }

            $activeVersion->load(['sections.questions.options']);
            $draft = SurveyVersion::query()->create([
                'number' => (int) SurveyVersion::query()->max('number') + 1,
                'status' => SurveyVersion::STATUS_DRAFT,
                'created_by' => $user->id,
            ]);

            foreach ($activeVersion->sections as $section) {
                $draftSection = $draft->sections()->create([
                    'stable_id' => $section->stable_id ?: (string) Str::uuid(),
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => $section->sort_order,
                    'is_active' => $section->is_active,
                ]);

                foreach ($section->questions as $question) {
                    $draftQuestion = $draft->questions()->create([
                        'survey_section_id' => $draftSection->id,
                        'stable_id' => $question->stable_id ?: (string) Str::uuid(),
                        'key' => $question->key,
                        'section' => $draftSection->title,
                        'prompt' => $question->prompt,
                        'help_text' => $question->help_text,
                        'type' => $question->type,
                        'is_required' => $question->is_required,
                        'max_selections' => $question->max_selections,
                        'sort_order' => $question->sort_order,
                        'is_active' => $question->is_active,
                    ]);

                    foreach ($question->options as $option) {
                        $draftQuestion->options()->create([
                            'stable_id' => $option->stable_id ?: (string) Str::uuid(),
                            'label' => $option->label,
                            'icon' => $option->icon,
                            'tone' => $option->tone,
                            'group_name' => $option->group_name,
                            'sort_order' => $option->sort_order,
                            'is_active' => $option->is_active,
                        ]);
                    }
                }
            }

            return $draft;
        }, attempts: 3);
    }

    /** @return array<string, mixed> */
    public function managementState(): array
    {
        $version = $this->editingVersion();
        $version->load([
            'sections' => fn ($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id')
                ->with([
                    'questions' => fn ($questionQuery) => $questionQuery
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->with(['options' => fn ($optionQuery) => $optionQuery->orderBy('sort_order')->orderBy('id')]),
                ]),
        ]);
        $activeVersion = $this->activeVersion();
        $sections = $version->sections->map(fn (SurveySection $section): array => [
            'id' => $section->id,
            'stableId' => $section->stable_id,
            'title' => $section->title,
            'description' => $section->description ?? '',
            'position' => $section->sort_order,
            'isActive' => $section->is_active,
            'questionCount' => $section->questions->count(),
            'questions' => $section->questions->map(fn (SurveyQuestion $question): array => [
                'id' => $question->id,
                'stableId' => $question->stable_id,
                'key' => $question->key,
                'prompt' => $question->prompt,
                'helpText' => $question->help_text ?? '',
                'type' => $question->type,
                'typeLabel' => SurveyQuestion::typeLabels()[$question->type] ?? $question->type,
                'isRequired' => $question->is_required,
                'isActive' => $question->is_active,
                'maxSelections' => $question->max_selections,
                'position' => $question->sort_order,
                'sectionStableId' => $section->stable_id,
                'options' => $question->options->map(fn (SurveyQuestionOption $option): array => [
                    'id' => $option->id,
                    'stableId' => $option->stable_id,
                    'label' => $option->label,
                    'icon' => $option->icon,
                    'tone' => $option->tone,
                    'groupName' => $option->group_name ?? '',
                    'position' => $option->sort_order,
                    'isActive' => $option->is_active,
                ])->values()->all(),
            ])->values()->all(),
        ])->values();

        return [
            'version' => [
                'number' => $version->number,
                'status' => $version->status,
                'activeNumber' => $activeVersion->number,
                'hasDraft' => $version->status === SurveyVersion::STATUS_DRAFT,
            ],
            'totalQuestions' => $sections->sum('questionCount'),
            'activeQuestions' => $sections
                ->where('isActive', true)
                ->sum(fn (array $section): int => collect($section['questions'])->where('isActive', true)->count()),
            'sections' => $sections->all(),
        ];
    }

    /** @param array<string, mixed> $data */
    public function saveQuestion(array $data, User $user, ?SurveyQuestion $sourceQuestion = null): SurveyQuestion
    {
        return DB::transaction(function () use ($data, $user, $sourceQuestion): SurveyQuestion {
            $draft = $this->draft($user);
            $section = $this->sectionInVersion($draft, (string) $data['section_stable_id']);
            $question = $sourceQuestion
                ? $this->questionInVersion($draft, $sourceQuestion->stable_id)
                : null;
            $oldSection = $question?->surveySection;
            $requestedPosition = (int) ($data['position'] ?? PHP_INT_MAX);

            if ($question && $oldSection && $oldSection->isNot($section)) {
                $question->update(['sort_order' => PHP_INT_MAX]);
                $this->normalizeQuestionPositions($oldSection);
            }

            $otherQuestions = $section->questions()
                ->when($question, fn ($query) => $query->where('id', '!=', $question->id))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $position = max(1, min($requestedPosition, $otherQuestions->count() + 1));

            foreach ($otherQuestions->values() as $index => $otherQuestion) {
                $targetPosition = $index + 1 >= $position ? $index + 2 : $index + 1;
                $otherQuestion->update(['sort_order' => $targetPosition]);
            }

            $attributes = [
                'survey_version_id' => $draft->id,
                'survey_section_id' => $section->id,
                'section' => $section->title,
                'prompt' => $data['prompt'],
                'help_text' => $data['help_text'] ?? null,
                'type' => $data['type'],
                'is_required' => (bool) $data['is_required'],
                'is_active' => (bool) $data['is_active'],
                'max_selections' => $data['type'] === SurveyQuestion::TYPE_MULTIPLE_CHOICE
                    ? (int) $data['max_selections']
                    : null,
                'sort_order' => $position,
            ];

            if ($question) {
                $question->update($attributes);
            } else {
                $question = $draft->questions()->create([
                    ...$attributes,
                    'stable_id' => (string) Str::uuid(),
                    'key' => $this->generateKey($draft, $data['prompt']),
                ]);
            }

            $this->syncOptions($question, $data['options'] ?? []);
            $this->normalizeQuestionPositions($section);

            return $question->fresh(['options', 'surveySection']);
        }, attempts: 3);
    }

    public function deleteQuestion(SurveyQuestion $sourceQuestion, User $user): void
    {
        DB::transaction(function () use ($sourceQuestion, $user): void {
            $draft = $this->draft($user);
            $question = $this->questionInVersion($draft, $sourceQuestion->stable_id);
            $section = $question->surveySection;
            $question->delete();
            $this->normalizeQuestionPositions($section);
        }, attempts: 3);
    }

    public function duplicateQuestion(SurveyQuestion $sourceQuestion, User $user): SurveyQuestion
    {
        return DB::transaction(function () use ($sourceQuestion, $user): SurveyQuestion {
            $draft = $this->draft($user);
            $question = $this->questionInVersion($draft, $sourceQuestion->stable_id);
            $section = $question->surveySection;
            $followingQuestions = $section->questions()
                ->where('sort_order', '>', $question->sort_order)
                ->orderByDesc('sort_order')
                ->get();

            foreach ($followingQuestions as $followingQuestion) {
                $followingQuestion->increment('sort_order');
            }

            $copy = $draft->questions()->create([
                'survey_section_id' => $section->id,
                'stable_id' => (string) Str::uuid(),
                'key' => $this->generateKey($draft, $question->prompt),
                'section' => $section->title,
                'prompt' => $question->prompt,
                'help_text' => $question->help_text,
                'type' => $question->type,
                'is_required' => $question->is_required,
                'max_selections' => $question->max_selections,
                'sort_order' => $question->sort_order + 1,
                'is_active' => $question->is_active,
            ]);

            foreach ($question->options as $option) {
                $copy->options()->create([
                    'stable_id' => (string) Str::uuid(),
                    'label' => $option->label,
                    'icon' => $option->icon,
                    'tone' => $option->tone,
                    'group_name' => $option->group_name,
                    'sort_order' => $option->sort_order,
                    'is_active' => $option->is_active,
                ]);
            }

            return $copy->fresh(['options', 'surveySection']);
        }, attempts: 3);
    }

    /** @param array<int, array{section_stable_id: string, question_stable_ids: array<int, string>}> $groups */
    public function reorderQuestions(array $groups, User $user): void
    {
        DB::transaction(function () use ($groups, $user): void {
            $draft = $this->draft($user);
            $questions = $draft->questions()->get()->keyBy('stable_id');
            $submittedIds = collect($groups)->flatMap(fn (array $group): array => $group['question_stable_ids'])->values();

            if ($submittedIds->duplicates()->isNotEmpty() || $submittedIds->sort()->values()->all() !== $questions->keys()->sort()->values()->all()) {
                throw ValidationException::withMessages([
                    'sections' => 'The question order changed in another window. Refresh and try again.',
                ]);
            }

            foreach ($groups as $group) {
                $section = $this->sectionInVersion($draft, $group['section_stable_id']);

                foreach ($group['question_stable_ids'] as $index => $stableId) {
                    $questions[$stableId]->update([
                        'survey_section_id' => $section->id,
                        'section' => $section->title,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }, attempts: 3);
    }

    public function moveQuestion(SurveyQuestion $sourceQuestion, string $direction, User $user): void
    {
        DB::transaction(function () use ($sourceQuestion, $direction, $user): void {
            $draft = $this->draft($user);
            $question = $this->questionInVersion($draft, $sourceQuestion->stable_id);
            $ordered = $question->surveySection->questions()->orderBy('sort_order')->orderBy('id')->get();
            $index = $ordered->search(fn (SurveyQuestion $item): bool => $item->is($question));
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

            if ($index === false || ! $ordered->has($targetIndex)) {
                return;
            }

            $target = $ordered[$targetIndex];
            $questionPosition = $question->sort_order;
            $question->update(['sort_order' => $target->sort_order]);
            $target->update(['sort_order' => $questionPosition]);
            $this->normalizeQuestionPositions($question->surveySection);
        }, attempts: 3);
    }

    /** @param array<string, mixed> $data */
    public function saveSection(array $data, User $user, ?SurveySection $sourceSection = null): SurveySection
    {
        return DB::transaction(function () use ($data, $user, $sourceSection): SurveySection {
            $draft = $this->draft($user);
            $section = $sourceSection
                ? $this->sectionInVersion($draft, $sourceSection->stable_id)
                : null;
            $others = $draft->sections()
                ->when($section, fn ($query) => $query->where('id', '!=', $section->id))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $position = max(1, min((int) $data['position'], $others->count() + 1));

            foreach ($others->values() as $index => $otherSection) {
                $otherSection->update([
                    'sort_order' => $index + 1 >= $position ? $index + 2 : $index + 1,
                ]);
            }

            $attributes = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'sort_order' => $position,
                'is_active' => (bool) $data['is_active'],
            ];

            if ($section) {
                $section->update($attributes);
                $section->questions()->update(['section' => $section->title]);
            } else {
                $section = $draft->sections()->create([
                    ...$attributes,
                    'stable_id' => (string) Str::uuid(),
                ]);
            }

            $this->normalizeSectionPositions($draft);

            return $section->fresh();
        }, attempts: 3);
    }

    public function deleteSection(SurveySection $sourceSection, User $user): void
    {
        DB::transaction(function () use ($sourceSection, $user): void {
            $draft = $this->draft($user);
            $section = $this->sectionInVersion($draft, $sourceSection->stable_id);

            if ($section->questions()->exists()) {
                throw ValidationException::withMessages([
                    'section' => 'Move or delete every question in this section before deleting it.',
                ]);
            }

            $section->delete();
            $this->normalizeSectionPositions($draft);
        }, attempts: 3);
    }

    /** @param array<int, string> $stableIds */
    public function reorderSections(array $stableIds, User $user): void
    {
        DB::transaction(function () use ($stableIds, $user): void {
            $draft = $this->draft($user);
            $sections = $draft->sections()->get()->keyBy('stable_id');

            if (count($stableIds) !== count(array_unique($stableIds)) || collect($stableIds)->sort()->values()->all() !== $sections->keys()->sort()->values()->all()) {
                throw ValidationException::withMessages([
                    'section_stable_ids' => 'The section order changed in another window. Refresh and try again.',
                ]);
            }

            foreach ($stableIds as $index => $stableId) {
                $sections[$stableId]->update(['sort_order' => $index + 1]);
            }
        }, attempts: 3);
    }

    public function moveSection(SurveySection $sourceSection, string $direction, User $user): void
    {
        DB::transaction(function () use ($sourceSection, $direction, $user): void {
            $draft = $this->draft($user);
            $section = $this->sectionInVersion($draft, $sourceSection->stable_id);
            $ordered = $draft->sections()->orderBy('sort_order')->orderBy('id')->get();
            $index = $ordered->search(fn (SurveySection $item): bool => $item->is($section));
            $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

            if ($index === false || ! $ordered->has($targetIndex)) {
                return;
            }

            $target = $ordered[$targetIndex];
            $sectionPosition = $section->sort_order;
            $section->update(['sort_order' => $target->sort_order]);
            $target->update(['sort_order' => $sectionPosition]);
            $this->normalizeSectionPositions($draft);
        }, attempts: 3);
    }

    public function publish(User $user): SurveyVersion
    {
        return DB::transaction(function () use ($user): SurveyVersion {
            $draft = SurveyVersion::query()->draft()->lockForUpdate()->first();

            if (! $draft) {
                throw ValidationException::withMessages(['survey' => 'There are no draft changes to publish.']);
            }

            $draft->load(['sections.questions.options']);
            $activeSections = $draft->sections->where('is_active', true);
            $activeQuestions = $activeSections->flatMap(fn (SurveySection $section) => $section->questions->where('is_active', true));

            if ($activeSections->isEmpty() || $activeQuestions->isEmpty()) {
                throw ValidationException::withMessages(['survey' => 'The survey needs at least one visible section and question.']);
            }

            foreach ($activeQuestions as $question) {
                if (blank($question->prompt)) {
                    throw ValidationException::withMessages(['survey' => 'Every visible question needs question text.']);
                }

                if ($question->type !== SurveyQuestion::TYPE_LONG_TEXT && $question->options->where('is_active', true)->count() < 2) {
                    throw ValidationException::withMessages(['survey' => 'Every visible choice question needs at least two answer options.']);
                }

                if ($question->type === SurveyQuestion::TYPE_MULTIPLE_CHOICE && $question->max_selections > $question->options->where('is_active', true)->count()) {
                    throw ValidationException::withMessages(['survey' => 'A selection limit cannot exceed the number of answer options.']);
                }
            }

            SurveyVersion::query()->active()->lockForUpdate()->update([
                'status' => SurveyVersion::STATUS_ARCHIVED,
            ]);
            $draft->update([
                'status' => SurveyVersion::STATUS_ACTIVE,
                'published_at' => now(),
                'created_by' => $user->id,
            ]);

            return $draft->fresh();
        }, attempts: 3);
    }

    private function questionInVersion(SurveyVersion $version, ?string $stableId): SurveyQuestion
    {
        return $version->questions()->where('stable_id', $stableId)->firstOrFail();
    }

    private function sectionInVersion(SurveyVersion $version, string $stableId): SurveySection
    {
        return $version->sections()->where('stable_id', $stableId)->firstOrFail();
    }

    private function normalizeQuestionPositions(SurveySection $section): void
    {
        $section->questions()->orderBy('sort_order')->orderBy('id')->get()
            ->each(fn (SurveyQuestion $question, int $index) => $question->update(['sort_order' => $index + 1]));
    }

    private function normalizeSectionPositions(SurveyVersion $version): void
    {
        $version->sections()->orderBy('sort_order')->orderBy('id')->get()
            ->each(fn (SurveySection $section, int $index) => $section->update(['sort_order' => $index + 1]));
    }

    private function generateKey(SurveyVersion $version, string $prompt): string
    {
        $baseKey = Str::limit(Str::slug($prompt, '_'), 64, '') ?: 'question';
        $key = $baseKey;
        $suffix = 2;

        while ($version->questions()->withTrashed()->where('key', $key)->exists()) {
            $key = $baseKey.'_'.$suffix;
            $suffix++;
        }

        return $key;
    }

    /** @param array<int, array<string, mixed>> $options */
    private function syncOptions(SurveyQuestion $question, array $options): void
    {
        if ($question->type === SurveyQuestion::TYPE_LONG_TEXT) {
            $question->options()->delete();

            return;
        }

        $retainedOptionIds = [];

        foreach (array_values($options) as $index => $optionData) {
            $stableId = filled($optionData['stable_id'] ?? null)
                ? (string) $optionData['stable_id']
                : (string) Str::uuid();
            $option = $question->options()->where('stable_id', $stableId)->first();
            $attributes = [
                ...Arr::only($optionData, ['label', 'icon', 'tone']),
                'stable_id' => $stableId,
                'group_name' => $question->type === SurveyQuestion::TYPE_GROUPED_CHOICE
                    ? $optionData['group_name']
                    : null,
                'sort_order' => $index + 1,
                'is_active' => true,
            ];

            if ($option) {
                $option->update($attributes);
            } else {
                $option = $question->options()->create($attributes);
            }

            $retainedOptionIds[] = $option->id;
        }

        $question->options()
            ->when($retainedOptionIds !== [], fn ($query) => $query->whereNotIn('id', $retainedOptionIds))
            ->delete();
    }
}
