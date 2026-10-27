<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedback_themes', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        $now = now();

        DB::table('feedback_themes')->insert([
            ['key' => 'scheduling', 'label' => 'Scheduling', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'technical_access', 'label' => 'Technical access', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'speaking_opportunities', 'label' => 'Speaking opportunities', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'teaching_group_experience', 'label' => 'Teaching/group experience', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'topics', 'label' => 'Topics', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'other', 'label' => 'Other', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_themes');
    }
};
