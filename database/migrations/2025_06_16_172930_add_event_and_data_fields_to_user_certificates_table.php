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
        Schema::table('user_certificates', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->after('user_id')->constrained('events');
            $table->json('certificate_data')->nullable()->after('certificate_path'); // store certificate details
            $table->timestamp('issued_at')->nullable()->after('certificate_data'); // when certificate was issued
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_certificates', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn(['event_id', 'certificate_data', 'issued_at']);
        });
    }
};
