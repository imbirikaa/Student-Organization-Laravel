<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Community;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Community::class)->constrained(); 
            $table->string('event');
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('last_application_datetime')->nullable();
            $table->string('location')->nullable();
            $table->string('certificate_type', 50)->nullable();
            $table->integer('min_sessions_for_certificate')->nullable();
            $table->string('verification_type', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
