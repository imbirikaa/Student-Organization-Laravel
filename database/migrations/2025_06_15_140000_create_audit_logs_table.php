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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('community_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action'); // e.g., 'role_assigned', 'member_removed', 'event_created'
            $table->string('resource_type')->nullable(); // e.g., 'User', 'Event', 'Community'
            $table->unsignedBigInteger('resource_id')->nullable(); // ID of the affected resource
            $table->json('old_values')->nullable(); // Previous state
            $table->json('new_values')->nullable(); // New state
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable(); // Human-readable description
            $table->timestamps();

            // Indexes for performance
            $table->index(['community_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['resource_type', 'resource_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
