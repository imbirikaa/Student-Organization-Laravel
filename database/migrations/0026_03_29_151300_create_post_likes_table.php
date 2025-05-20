<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ForumPost;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table) {
            $table->foreignIdFor(ForumPost::class, 'post_id')->constrained('forum_posts');
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->dateTime('liked_at')->nullable();
            $table->primary(['post_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
