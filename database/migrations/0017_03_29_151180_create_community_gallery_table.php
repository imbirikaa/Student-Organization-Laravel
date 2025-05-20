<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Community;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_gallery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(Community::class)->constrained(); 
            $table->string('image');
            $table->dateTime('upload_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_gallery');
    }
};
