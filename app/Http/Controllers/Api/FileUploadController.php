<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
  /**
   * Upload a file and associate it with a model.
   */
  public function upload(Request $request): JsonResponse
  {
    $request->validate([
      'file' => 'required|file|max:10240', // 10MB max
      'uploadable_type' => 'required|string',
      'uploadable_id' => 'required|integer',
      'upload_type' => 'required|string',
      'description' => 'nullable|string|max:500',
      'is_public' => 'boolean'
    ]);

    $user = $request->user();
    if (!$user) {
      return response()->json(['message' => 'Authentication required'], 401);
    }

    $file = $request->file('file');
    $uploadableType = $request->input('uploadable_type');
    $uploadableId = $request->input('uploadable_id');
    $uploadType = $request->input('upload_type');

    // Validate uploadable model exists
    if (!class_exists($uploadableType)) {
      return response()->json(['message' => 'Invalid uploadable type'], 400);
    }

    $uploadableModel = $uploadableType::find($uploadableId);
    if (!$uploadableModel) {
      return response()->json(['message' => 'Uploadable model not found'], 404);
    }

    // Generate unique filename
    $originalName = $file->getClientOriginalName();
    $extension = $file->getClientOriginalExtension();
    $filename = time() . '_' . Str::random(10) . '.' . $extension;

    // Create storage path
    $path = "uploads/{$uploadType}/" . date('Y/m/d') . '/' . $filename;

    // Store the file
    $storedPath = $file->storeAs(dirname($path), basename($path), 'public');

    // Create file upload record
    $fileUpload = FileUpload::create([
      'original_name' => $originalName,
      'filename' => $filename,
      'path' => $storedPath,
      'mime_type' => $file->getMimeType(),
      'size' => $file->getSize(),
      'disk' => 'public',
      'uploadable_type' => $uploadableType,
      'uploadable_id' => $uploadableId,
      'upload_type' => $uploadType,
      'uploaded_by' => $user->id,
      'is_public' => $request->boolean('is_public', true),
      'description' => $request->input('description')
    ]);

    return response()->json([
      'message' => 'File uploaded successfully',
      'file' => $fileUpload,
      'url' => $fileUpload->url
    ], 201);
  }

  /**
   * Get files for a specific uploadable model.
   */
  public function getFiles(Request $request, string $uploadableType, int $uploadableId): JsonResponse
  {
    // Validate uploadable model exists
    if (!class_exists($uploadableType)) {
      return response()->json(['message' => 'Invalid uploadable type'], 400);
    }

    $uploadableModel = $uploadableType::find($uploadableId);
    if (!$uploadableModel) {
      return response()->json(['message' => 'Uploadable model not found'], 404);
    }

    $files = FileUpload::where('uploadable_type', $uploadableType)
      ->where('uploadable_id', $uploadableId)
      ->when($request->input('upload_type'), function ($query, $uploadType) {
        return $query->where('upload_type', $uploadType);
      })
      ->with('uploader:id,name,email')
      ->latest()
      ->get();

    return response()->json([
      'files' => $files->map(function ($file) {
        return [
          'id' => $file->id,
          'original_name' => $file->original_name,
          'filename' => $file->filename,
          'mime_type' => $file->mime_type,
          'size' => $file->size,
          'upload_type' => $file->upload_type,
          'is_public' => $file->is_public,
          'description' => $file->description,
          'url' => $file->url,
          'uploaded_by' => $file->uploader,
          'created_at' => $file->created_at,
          'updated_at' => $file->updated_at,
        ];
      })
    ]);
  }

  /**
   * Download a file.
   */
  public function downloadFile(Request $request, int $id): JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
  {
    $fileUpload = FileUpload::find($id);
    if (!$fileUpload) {
      return response()->json(['message' => 'File not found'], 404);
    }

    if (!$fileUpload->exists()) {
      return response()->json(['message' => 'File does not exist on disk'], 404);
    }
    $filePath = storage_path('app/' . $fileUpload->disk . '/' . $fileUpload->path);

    if (!file_exists($filePath)) {
      return response()->json(['message' => 'File does not exist on disk'], 404);
    }

    return response()->download($filePath, $fileUpload->original_name);
  }

  /**
   * Delete a file.
   */
  public function deleteFile(Request $request, int $id): JsonResponse
  {
    $user = $request->user();
    if (!$user) {
      return response()->json(['message' => 'Authentication required'], 401);
    }

    $fileUpload = FileUpload::find($id);
    if (!$fileUpload) {
      return response()->json(['message' => 'File not found'], 404);
    }

    // Check if user has permission to delete (either uploader or admin)
    if ($fileUpload->uploaded_by !== $user->id && !$user->hasRole('admin')) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Delete the file (this will also delete the physical file due to the model's boot method)
    $fileUpload->delete();

    return response()->json(['message' => 'File deleted successfully']);
  }
}
