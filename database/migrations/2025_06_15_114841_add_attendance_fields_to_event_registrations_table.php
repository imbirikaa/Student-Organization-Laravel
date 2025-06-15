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
            $table->string('attendance_code', 8)->unique()->nullable()->after('status');
            $table->string('qr_code_path')->nullable()->after('attendance_code');
            $table->timestamp('checked_in_at')->nullable()->after('qr_code_path');
            $table->string('checked_in_by')->nullable()->after('checked_in_at'); // Admin who checked them in
            $table->text('check_in_notes')->nullable()->after('checked_in_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_code',
                'qr_code_path',
                'checked_in_at',
                'checked_in_by',
                'check_in_notes'
            ]);
        });
    }
};
