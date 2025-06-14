<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This will add all the necessary columns that Laravel's 
     * notification system uses: 'data', 'read_at', and the polymorphic
     * 'notifiable' columns.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Check if the 'notifiable' columns don't already exist before trying to add them
            if (!Schema::hasColumn('notifications', 'notifiable_type') && !Schema::hasColumn('notifications', 'notifiable_id')) {
                // The morphs() helper adds both 'notifiable_type' (string) and
                // 'notifiable_id' (bigint) columns, which are required.
                $table->morphs('notifiable', 'notifiable_index');
            }

            // Check if the 'data' column doesn't already exist before trying to add it
            if (!Schema::hasColumn('notifications', 'data')) {
                // The 'data' column should be a TEXT type to store JSON data.
                $table->text('data');
            }
            
            // Check if the 'read_at' column doesn't already exist before trying to add it
            if (!Schema::hasColumn('notifications', 'read_at')) {
                // The 'read_at' column is a timestamp that marks when a notification was read.
                // It should be nullable because unread notifications will have a NULL value.
                $table->timestamp('read_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * This will safely remove the added columns if you ever need to
     * roll back this migration.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Check if the columns exist before trying to drop them
            if (Schema::hasColumn('notifications', 'notifiable_type')) {
                // The dropMorphs() helper removes both notifiable columns and their index.
                $table->dropMorphs('notifiable', 'notifiable_index');
            }
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
