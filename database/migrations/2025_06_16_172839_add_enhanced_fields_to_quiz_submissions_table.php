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
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->decimal('score', 5, 2)->nullable()->after('user_id'); // percentage score
            $table->json('answers')->nullable()->after('score'); // store selected answers
            $table->boolean('passed')->default(false)->after('answers'); // pass/fail status
            $table->timestamp('submitted_at')->nullable()->after('passed'); // submission timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_submissions', function (Blueprint $table) {
            $table->dropColumn(['score', 'answers', 'passed', 'submitted_at']);
        });
    }
};
