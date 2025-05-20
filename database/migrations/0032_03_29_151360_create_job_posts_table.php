<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(User::class, 'user_id')->constrained();
            $table->string('job');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->boolean('is_approved')->nullable()->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
