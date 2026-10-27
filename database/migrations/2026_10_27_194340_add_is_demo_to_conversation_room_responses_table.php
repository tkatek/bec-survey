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
            $table->boolean('is_demo')->default(false)->after('survey_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_room_responses', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
};
