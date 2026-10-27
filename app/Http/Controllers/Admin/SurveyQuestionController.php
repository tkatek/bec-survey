<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyQuestionRequest;
use App\Http\Requests\UpdateSurveyQuestionRequest;
use App\Models\SurveyQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SurveyQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $questions = SurveyQuestion::query()
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->withCount('answers')
            ->ordered()
            ->get();

        return view('admin.questions.index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.questions.create', [
            'question' => new SurveyQuestion([
                'section' => SurveyQuestion::sections()[0],
                'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
                'is_required' => true,
                'is_active' => true,
            ]),
            'sections' => SurveyQuestion::sections(),
            'types' => SurveyQuestion::typeLabels(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSurveyQuestionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $question = SurveyQuestion::create([
                ...Arr::except($validated, 'options'),
                'key' => $this->generateKey($validated['prompt']),
                'sort_order' => (int) SurveyQuestion::query()->max('sort_order') + 1,
                'max_selections' => $validated['type'] === SurveyQuestion::TYPE_MULTIPLE_CHOICE
                    ? $validated['max_selections']
                    : null,
            ]);

            $this->syncOptions($question, $validated['options'] ?? []);
        });

        return redirect()
            ->route('admin.questions.index')
            ->with('status', 'Question added to the survey.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(SurveyQuestion $question): View
    {
        $question->load(['options' => fn ($query) => $query->where('is_active', true)]);

        return view('admin.questions.edit', [
            'question' => $question,
            'sections' => SurveyQuestion::sections(),
            'types' => SurveyQuestion::typeLabels(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSurveyQuestionRequest $request, SurveyQuestion $question): RedirectResponse
    {
        $validated = $request->validated();

        if ($question->is_active && ! $validated['is_active'] && SurveyQuestion::query()->active()->count() <= 1) {
            return back()
                ->withInput()
                ->withErrors(['is_active' => 'The survey must keep at least one active question.']);
        }

        DB::transaction(function () use ($question, $validated): void {
            $question->update([
                ...Arr::except($validated, 'options'),
                'max_selections' => $validated['type'] === SurveyQuestion::TYPE_MULTIPLE_CHOICE
                    ? $validated['max_selections']
                    : null,
            ]);

            $this->syncOptions($question, $validated['options'] ?? []);
        });

        return redirect()
            ->route('admin.questions.index')
            ->with('status', 'Question updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SurveyQuestion $question): RedirectResponse
    {
        if ($question->is_active && SurveyQuestion::query()->active()->count() <= 1) {
            return back()->with('error', 'The survey must keep at least one active question.');
        }

        $question->delete();

        return redirect()
            ->route('admin.questions.index')
            ->with('status', 'Question removed from the live survey. Existing answers were preserved.');
    }

    private function generateKey(string $prompt): string
    {
        $baseKey = Str::limit(Str::slug($prompt, '_'), 64, '');
        $baseKey = $baseKey !== '' ? $baseKey : 'question';
        $key = $baseKey;
        $suffix = 2;

        while (SurveyQuestion::query()->withTrashed()->where('key', $key)->exists()) {
            $key = $baseKey.'_'.$suffix;
            $suffix++;
        }

        return $key;
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     */
    private function syncOptions(SurveyQuestion $question, array $options): void
    {
        $retainedOptionIds = [];

        foreach (array_values($options) as $index => $optionData) {
            $optionId = isset($optionData['id'])
                ? (int) $optionData['id']
                : null;
            $option = $optionId
                ? $question->options()->whereKey($optionId)->first()
                : null;

            $attributes = [
                ...Arr::only($optionData, ['label', 'icon', 'tone', 'group_name']),
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
