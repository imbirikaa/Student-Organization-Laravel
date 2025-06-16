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
        Schema::table('quizzes', function (Blueprint $table) {
            // Add missing fields that the controller expects
            $table->string('title')->nullable()->after('event_id');
            $table->text('description')->nullable()->after('title');
            $table->integer('passing_score')->default(60)->after('description');
            $table->integer('time_limit')->nullable()->after('passing_score'); // in minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'passing_score', 'time_limit']);
        });
    }
};
