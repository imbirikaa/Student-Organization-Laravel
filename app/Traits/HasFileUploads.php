<?php

namespace App\Traits;

use App\Models\FileUpload;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFileUploads
{
  /**
   * Get all file uploads for this model.
   */
  public function fileUploads(): MorphMany
  {
    return $this->morphMany(FileUpload::class, 'uploadable');
  }

  /**
   * Get file uploads by type.
   */
  public function fileUploadsByType(string $type)
  {
    return $this->fileUploads()->where('upload_type', $type);
  }

  /**
   * Get the latest file upload of a specific type.
   */
  public function latestFileUpload(string $type)
  {
    return $this->fileUploads()
      ->where('upload_type', $type)
      ->latest()
      ->first();
  }

  /**
   * Get the URL of the latest file upload of a specific type.
   */
  public function getFileUploadUrl(string $type): ?string
  {
    $fileUpload = $this->latestFileUpload($type);
    return $fileUpload ? $fileUpload->url : null;
  }

  /**
   * Delete all file uploads when the model is deleted.
   */
  protected static function bootHasFileUploads()
  {
    static::deleting(function ($model) {
      $model->fileUploads()->each(function ($fileUpload) {
        $fileUpload->delete(); // This will also delete the actual file due to the FileUpload model's boot method
      });
    });
  }
}
