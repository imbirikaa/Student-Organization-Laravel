<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EventSession;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('session_attendance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(EventSession::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->dateTime('attendance_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_attendance');
    }
};
