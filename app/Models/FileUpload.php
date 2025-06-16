<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FileUpload extends Model
{
  use HasFactory;

  protected $fillable = [
    'original_name',
    'filename',
    'path',
    'mime_type',
    'size',
    'disk',
    'uploadable_type',
    'uploadable_id',
    'upload_type',
    'uploaded_by',
    'is_public',
    'description',
    'metadata'
  ];

  protected $casts = [
    'is_public' => 'boolean',
    'size' => 'integer',
    'metadata' => 'array',
  ];

  /**
   * Get the owning uploadable model.
   */
  public function uploadable()
  {
    return $this->morphTo();
  }

  /**
   * Get the user who uploaded this file.
   */
  public function uploader()
  {
    return $this->belongsTo(User::class, 'uploaded_by');
  }
  /**
   * Get the URL for this file.
   */
  public function getUrlAttribute()
  {
    if ($this->disk === 'public') {
      return asset('storage/' . $this->path);
    }

    return Storage::url($this->path);
  }

  /**
   * Get the full file path.
   */
  public function getFullPathAttribute()
  {
    return storage_path('app/' . $this->disk . '/' . $this->path);
  }

  /**
   * Check if the file exists.
   */
  public function exists()
  {
    return Storage::disk($this->disk)->exists($this->path);
  }

  /**
   * Delete the file from storage.
   */
  public function deleteFile()
  {
    if ($this->exists()) {
      Storage::disk($this->disk)->delete($this->path);
    }
  }

  /**
   * Boot the model.
   */
  protected static function boot()
  {
    parent::boot();

    // Delete the actual file when the model is deleted
    static::deleting(function ($fileUpload) {
      $fileUpload->deleteFile();
    });
  }
}
