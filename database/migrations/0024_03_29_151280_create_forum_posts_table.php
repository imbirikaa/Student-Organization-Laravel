<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ForumTopic;
use App\Models\User;
use App\Models\ForumPost;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(ForumTopic::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignId('parent_post_id')->nullable()->constrained('forum_posts', 'id')->nullOnDelete();
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
    }
};
