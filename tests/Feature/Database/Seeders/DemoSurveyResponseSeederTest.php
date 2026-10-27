<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\ConversationRoomResponse;
use Database\Seeders\DemoSurveyResponseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DemoSurveyResponseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeder_replaces_only_the_twenty_complete_demo_responses(): void
    {
        $realResponse = ConversationRoomResponse::factory()->create();

        $this->seed(DemoSurveyResponseSeeder::class);
        $this->seed(DemoSurveyResponseSeeder::class);

        $demoResponses = ConversationRoomResponse::query()
            ->where('is_demo', true)
            ->withCount('answers')
            ->get();

        $this->assertCount(20, $demoResponses);
        $this->assertTrue($demoResponses->every(fn (ConversationRoomResponse $response): bool => $response->answers_count >= 14));
        $this->assertModelExists($realResponse);
        $this->assertDatabaseCount('conversation_room_responses', 21);
    }
}
