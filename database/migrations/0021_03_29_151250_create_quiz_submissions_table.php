<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Quiz;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Quiz::class, 'quiz_id')->constrained();
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->dateTime('submission_datetime')->nullable();
            $table->integer('correct_count')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_submissions');
    }
};
