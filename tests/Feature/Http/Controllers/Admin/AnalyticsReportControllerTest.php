<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Database\Seeders\FeedbackThemeSeeder;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AnalyticsReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SurveyQuestionSeeder::class, FeedbackThemeSeeder::class]);
    }

    public function test_pdf_report_is_a_protected_filtered_attachment_from_the_dashboard_snapshot(): void
    {
        $query = [
            'preset' => 'month',
        ];

        $this->post(route('admin.reports.pdf'), $query)
            ->assertRedirect(route('login'));

        $admin = User::factory()->administrator()->create();
        $dashboard = $this->actingAs($admin)->get(route('dashboard', $query))->assertOk();
        $snapshot = $dashboard->viewData('snapshotToken');
        $report = $this->actingAs($admin)->post(route('admin.reports.pdf'), $query + [
            'snapshot' => $snapshot,
        ]);

        $report->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="BEC_Conversation_Room_Report_'.now()->startOfMonth()->toDateString().'_to_'.now()->toDateString().'.pdf"');
        $this->assertStringStartsWith('%PDF-', $report->getContent());
    }

    public function test_pdf_report_rejects_non_administrators(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.reports.pdf'), [
                'preset' => 'month',
            ])
            ->assertForbidden();
    }
}
