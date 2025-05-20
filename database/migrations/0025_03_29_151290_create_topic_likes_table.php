<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ForumTopic;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_likes', function (Blueprint $table) {
            $table->foreignIdFor(ForumTopic::class, 'topic_id')->constrained('forum_topics'); 
            $table->foreignIdFor(User::class, 'user_id')->constrained(); 
            $table->dateTime('liked_at')->nullable();
            $table->primary(['topic_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_likes');
    }
};
