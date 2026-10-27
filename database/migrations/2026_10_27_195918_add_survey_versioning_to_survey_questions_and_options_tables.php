<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropUnique('survey_questions_key_unique');
            $table->foreignId('survey_version_id')->nullable()->after('id');
            $table->foreignId('survey_section_id')->nullable()->after('survey_version_id');
            $table->uuid('stable_id')->nullable()->after('survey_section_id');
        });

        Schema::table('survey_question_options', function (Blueprint $table) {
            $table->uuid('stable_id')->nullable()->after('survey_question_id');
        });

        $versionNumber = (int) (DB::table('conversation_room_responses')->max('survey_version') ?: 2);
        $versionId = DB::table('survey_versions')->insertGetId([
            'number' => $versionNumber,
            'status' => 'active',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $knownSections = [
            'Attendance and participation' => [
                'title' => 'Attendance & Participation',
                'description' => 'Questions about attendance and participation in the Conversation Room.',
            ],
            'The updated Conversation Room' => [
                'title' => 'The Updated Conversation Room',
                'description' => 'Questions about the updated Conversation Room experience.',
            ],
            'Your preferences' => [
                'title' => 'Your Preferences',
                'description' => 'Questions about preferred times, days and improvements.',
            ],
            'Your feedback' => [
                'title' => 'Your Feedback',
                'description' => 'Final questions about future attendance and open feedback.',
            ],
        ];
        $sectionRows = DB::table('survey_questions')
            ->select('section', DB::raw('MIN(sort_order) as first_position'))
            ->whereNull('deleted_at')
            ->groupBy('section')
            ->orderBy('first_position')
            ->get();
        $sectionIds = [];

        foreach ($sectionRows as $sectionIndex => $sectionRow) {
            $knownSection = $knownSections[$sectionRow->section] ?? null;
            $title = $knownSection['title'] ?? $sectionRow->section;
            $sectionIds[$sectionRow->section] = DB::table('survey_sections')->insertGetId([
                'survey_version_id' => $versionId,
                'stable_id' => (string) Str::uuid(),
                'title' => $title,
                'description' => $knownSection['description'] ?? null,
                'sort_order' => $sectionIndex + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($sectionRows as $sectionRow) {
            $knownSection = $knownSections[$sectionRow->section] ?? null;
            $title = $knownSection['title'] ?? $sectionRow->section;
            $questions = DB::table('survey_questions')
                ->where('section', $sectionRow->section)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($questions as $questionIndex => $question) {
                DB::table('survey_questions')->where('id', $question->id)->update([
                    'survey_version_id' => $versionId,
                    'survey_section_id' => $sectionIds[$sectionRow->section],
                    'stable_id' => (string) Str::uuid(),
                    'section' => $title,
                    'sort_order' => $questionIndex + 1,
                ]);
            }
        }

        DB::table('survey_question_options')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $option): void {
                DB::table('survey_question_options')->where('id', $option->id)->update([
                    'stable_id' => (string) Str::uuid(),
                ]);
            });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->foreign('survey_version_id')->references('id')->on('survey_versions')->restrictOnDelete();
            $table->foreign('survey_section_id')->references('id')->on('survey_sections')->restrictOnDelete();
            $table->unique(['survey_version_id', 'key'], 'survey_questions_version_key_unique');
            $table->unique(['survey_version_id', 'stable_id'], 'survey_questions_version_stable_unique');
            $table->index(
                ['survey_version_id', 'survey_section_id', 'is_active', 'sort_order'],
                'survey_questions_version_section_order_idx',
            );
        });

        Schema::table('survey_question_options', function (Blueprint $table) {
            $table->unique(
                ['survey_question_id', 'stable_id'],
                'survey_question_options_question_stable_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_question_options', function (Blueprint $table) {
            $table->dropUnique('survey_question_options_question_stable_unique');
            $table->dropColumn('stable_id');
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropIndex('survey_questions_version_section_order_idx');
            $table->dropUnique('survey_questions_version_stable_unique');
            $table->dropUnique('survey_questions_version_key_unique');
            $table->dropForeign(['survey_section_id']);
            $table->dropForeign(['survey_version_id']);
            $table->dropColumn(['stable_id', 'survey_section_id', 'survey_version_id']);
            $table->unique('key');
        });
    }
};
