<?php

namespace Database\Seeders;

use App\Models\FeedbackTheme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeedbackThemeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            'scheduling' => 'Scheduling',
            'technical_access' => 'Technical access',
            'speaking_opportunities' => 'Speaking opportunities',
            'teaching_group_experience' => 'Teaching/group experience',
            'topics' => 'Topics',
            'other' => 'Other',
        ])->each(fn (string $label, string $key) => FeedbackTheme::query()->updateOrCreate(
            ['key' => $key],
            ['label' => $label],
        ));
    }
}
