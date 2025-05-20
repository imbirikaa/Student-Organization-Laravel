<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('community');
            $table->string('logo')->nullable();
            $table->text('about')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->year('founding_year')->nullable();
            $table->text('achievements')->nullable();
            $table->text('traditional_events')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('sponsors')->nullable();
            $table->text('faq')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
