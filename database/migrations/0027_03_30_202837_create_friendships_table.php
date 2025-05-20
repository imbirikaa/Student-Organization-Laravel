<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'friend_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->nullable()->default('pending');
            $table->dateTime('request_date')->nullable();
            $table->dateTime('response_date')->nullable();
            $table->primary(['user_id', 'friend_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
