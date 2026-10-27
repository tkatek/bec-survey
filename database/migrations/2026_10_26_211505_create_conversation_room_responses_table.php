<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_room_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('survey_version')->default(1);
            $table->string('attendance_frequency', 40);
            $table->string('attendance_change', 80);
            $table->string('updated_room_use', 60);
            $table->string('ease_of_use', 40);
            $table->string('activity_usefulness', 40);
            $table->string('speaking_comfort', 60);
            $table->string('session_format', 80);
            $table->string('preferred_session_time', 60);
            $table->string('time_zone', 20);
            $table->string('session_length', 20);
            $table->string('attendance_likelihood', 40);
            $table->text('one_change')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'id'], 'cr_responses_created_id_idx');
            $table->index(['attendance_frequency', 'created_at'], 'cr_responses_attendance_created_idx');
            $table->index(['updated_room_use', 'created_at'], 'cr_responses_room_use_created_idx');
            $table->index(['session_format', 'created_at'], 'cr_responses_format_created_idx');
            $table->index(['preferred_session_time', 'time_zone'], 'cr_responses_time_zone_idx');
            $table->index(['attendance_likelihood', 'created_at'], 'cr_responses_likelihood_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_room_responses');
    }
};
