<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Community;
use App\Models\User;
use App\Models\CommunityRole;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_memberships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Community::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(CommunityRole::class)->nullable()->constrained();
            $table->string('status', 20)->nullable()->default('pending');
            $table->dateTime('application_date')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_memberships');
    }
};
