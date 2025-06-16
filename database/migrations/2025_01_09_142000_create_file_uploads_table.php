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
    Schema::create('file_uploads', function (Blueprint $table) {
      $table->id();
      $table->string('original_name');
      $table->string('filename');
      $table->string('path');
      $table->string('mime_type');
      $table->bigInteger('size');
      $table->string('disk')->default('public');
      $table->morphs('uploadable'); // Creates uploadable_type and uploadable_id
      $table->string('upload_type')->nullable(); // e.g., 'logo', 'cover_image', 'profile_picture'
      $table->unsignedBigInteger('uploaded_by');
      $table->boolean('is_public')->default(true);
      $table->text('description')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamps();

      $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
      $table->index(['uploadable_type', 'uploadable_id']);
      $table->index(['upload_type']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('file_uploads');
  }
};
