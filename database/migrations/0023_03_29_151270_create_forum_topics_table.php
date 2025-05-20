<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ForumCategory;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(ForumCategory::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('topic');
            $table->text('content')->nullable();
            $table->timestamps();
            $table->integer('pinned_priority')->nullable()->default(0);
            $table->dateTime('last_bump_time')->nullable();
            $table->boolean('is_active')->nullable()->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_topics');
    }
};
