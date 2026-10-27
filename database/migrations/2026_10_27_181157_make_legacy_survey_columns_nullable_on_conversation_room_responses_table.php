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
        Schema::table('conversation_room_responses', function (Blueprint $table) {
            $table->string('attendance_frequency', 40)->nullable()->change();
            $table->string('attendance_change', 80)->nullable()->change();
            $table->string('updated_room_use', 60)->nullable()->change();
            $table->string('ease_of_use', 40)->nullable()->change();
            $table->string('activity_usefulness', 40)->nullable()->change();
            $table->string('speaking_comfort', 60)->nullable()->change();
            $table->string('session_format', 80)->nullable()->change();
            $table->string('preferred_session_time', 60)->nullable()->change();
            $table->string('time_zone', 20)->nullable()->change();
            $table->string('session_length', 20)->nullable()->change();
            $table->string('attendance_likelihood', 40)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_room_responses', function (Blueprint $table) {
            $table->string('attendance_frequency', 40)->nullable(false)->change();
            $table->string('attendance_change', 80)->nullable(false)->change();
            $table->string('updated_room_use', 60)->nullable(false)->change();
            $table->string('ease_of_use', 40)->nullable(false)->change();
            $table->string('activity_usefulness', 40)->nullable(false)->change();
            $table->string('speaking_comfort', 60)->nullable(false)->change();
            $table->string('session_format', 80)->nullable(false)->change();
            $table->string('preferred_session_time', 60)->nullable(false)->change();
            $table->string('time_zone', 20)->nullable(false)->change();
            $table->string('session_length', 20)->nullable(false)->change();
            $table->string('attendance_likelihood', 40)->nullable(false)->change();
        });
    }
};
