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
            $table->string('title')->nullable()->after('event_id');
            $table->text('description')->nullable()->after('title');
            $table->integer('time_limit')->nullable()->after('description'); // in minutes
            $table->integer('passing_score')->default(70)->after('time_limit'); // percentage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'time_limit', 'passing_score']);
        });
    }
};
