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
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('attendance_code', 8)->unique()->nullable();
            $table->timestamp('check_in_time')->nullable();
            $table->string('check_in_method', 20)->nullable(); // 'qr_code', 'manual_code', 'admin'
            $table->boolean('attendance_confirmed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['attendance_code', 'check_in_time', 'check_in_method', 'attendance_confirmed']);
        });
    }
};
