<?php

namespace App\Http\Controllers\Backend;

use Exception;
use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\ImageUploadTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MediaController extends Controller
{
    use ImageUploadTrait;

    /**
     * Initialize and ensure media directory exists
     */
    public function __construct()
    {
        $mediaPath = storage_path('app/public/media');

        // Use trait method to ensure default folders exist
        $this->ensureDefaultMediaFoldersExist($mediaPath, true);
    }

    /**
     * Display media management page
     */
    public function index(Request $request)
    {
        // Return the view for regular page load
        $media = Media::paginate(20);
        return view('admin.media.index', compact('media'));
    }

    /**
     * List directory contents (folders and files)
     */
    public function getFiles(Request $request)
    {
        $path = $request->get('path', '');
        $baseMediaPath = storage_path('app/public/media');

        // Ensure base media directory and default folders exist using trait method
        $this->ensureDefaultMediaFoldersExist($baseMediaPath, true);

        // Get folders and files from filesystem using trait method
        $contents = $this->getDirectoryContents(
            $baseMediaPath,
            $path,
            false, // Don't include files from filesystem (we get them from DB)
            true   // Include folders
        );

        $folders = $contents['folders'];

        // Get files from database
        $query = Media::query();

        // Filter by folder path
        if ($path) {
            $query->where('folder_path', $path);
        } else {
            $query->whereNull('folder_path')->orWhere('folder_path', '');
        }

        // Include user information
        $mediaFiles = $query->with('user')->orderBy('created_at', 'desc')->get();        $files = [];
        foreach ($mediaFiles as $file) {
            $files[] = [
                'id' => $file->id,
                'file_name' => $file->file_name,
                'original_name' => $file->original_name,
                'file_path' => $file->file_path,
                'path' => $file->file_path,
                'file_url' => $file->file_url,
                'folder_path' => $file->folder_path,
                'metadata' => $file->metadata,
                'full_url' => $file->full_url,
                'type' => $file->file_type,
                'file_type' => $file->file_type,
                'size' => $file->formatted_size,
                'extension' => $file->file_extension,
                'modified' => $file->created_at->format('Y-m-d H:i:s'),
                'user' => $file->user ? $file->user->name : 'Unknown',
                'is_image' => $file->is_image,
                'mime_type' => $file->mime_type,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'folders' => $folders,
                'files' => $files,
            ],
        ]);
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|regex:/^[a-zA-Z0-9_-]+$/',
            'path' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid folder name. Use only letters, numbers, hyphens, and underscores.',
            ], 400);
        }

        $basePath = storage_path('app/public/media');
        $folderName = $request->get('name');
        $parentPath = $request->get('path', '');

        // Use trait method to create folder
        $result = $this->createFolderInPath($basePath, $folderName, $parentPath);

        return response()->json($result);
    }

    /**
     * Rename a folder
     */
    public function renameFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
            'new_name' => 'required|regex:/^[a-zA-Z0-9_-]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid folder name',
            ], 400);
        }

        $basePath = storage_path('app/public/media');
        $oldPath = $request->path;
        $newName = $request->new_name;

        // Use trait method to rename folder
        $result = $this->renameFolderInPath($basePath, $oldPath, $newName);

        if ($result['success']) {
            // Update database records for files in this folder
            $oldRelativePath = $oldPath;
            $newRelativePath = $result['path'];

            Media::where('folder_path', 'LIKE', $oldRelativePath . '%')
                ->update([
                    'folder_path' => \DB::raw("REPLACE(folder_path, '$oldRelativePath', '$newRelativePath')"),
                ]);
        }

        return response()->json($result);
    }

    /**
     * Delete a folder
     */
    public function deleteFolder(Request $request)
    {
        $path = $request->get('path');

        if (!$path) {
            return response()->json([
                'success' => false,
                'message' => 'Path is required',
            ], 400);
        }

        $basePath = storage_path('app/public/media');

        // Use trait method to delete folder
        $result = $this->deleteFolderInPath($basePath, $path, false);

        return response()->json($result);
    }

    /**
     * Move a folder to another location
     */
    public function moveFolderToFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
            'target_path' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
            ], 400);
        }

        try {
            $sourcePath = $request->get('path');
            $targetPath = $request->get('target_path', '');
            $basePath = storage_path('app/public/media');

            // Clean paths
            $sourcePath = trim($sourcePath, '/');
            $targetPath = $targetPath === '/' ? '' : trim($targetPath, '/');

            if (empty($sourcePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source path is required',
                ], 400);
            }

            $sourceFullPath = $basePath . '/' . $sourcePath;
            $folderName = basename($sourcePath);

            // Build destination path
            $destinationPath = $targetPath ? $targetPath . '/' . $folderName : $folderName;
            $destinationFullPath = $basePath . '/' . $destinationPath;

            // Check if source exists
            if (!File::exists($sourceFullPath) || !File::isDirectory($sourceFullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source folder not found',
                ], 404);
            }

            // Prevent moving folder into itself
            if (strpos($destinationPath, $sourcePath . '/') === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move a folder into itself',
                ], 400);
            }

            // Check if destination already exists
            if (File::exists($destinationFullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A folder with this name already exists in the destination',
                ], 400);
            }

            // Ensure target directory exists
            $targetFullPath = $targetPath ? $basePath . '/' . $targetPath : $basePath;
            if (!File::exists($targetFullPath)) {
                File::makeDirectory($targetFullPath, 0755, true);
            }

            // Move the folder
            File::move($sourceFullPath, $destinationFullPath);

            // Update database records for all files in the moved folder
            $oldFolderPath = $sourcePath;
            $newFolderPath = $destinationPath;

            // Update media records with paths starting with old folder path
            Media::where('folder_path', 'like', $oldFolderPath . '%')
                ->orWhere('folder_path', $oldFolderPath)
                ->get()
                ->each(function ($media) use ($oldFolderPath, $newFolderPath) {
                    // Update folder_path
                    if ($media->folder_path === $oldFolderPath) {
                        $media->folder_path = $newFolderPath;
                    } else {
                        $media->folder_path = str_replace($oldFolderPath, $newFolderPath, $media->folder_path);
                    }

                    // Update file_path
                    $media->file_path = str_replace('media/' . $oldFolderPath, 'media/' . $newFolderPath, $media->file_path);

                    // Update file_url
                    $media->file_url = str_replace($oldFolderPath, $newFolderPath, $media->file_url);

                    $media->save();
                });

            return response()->json([
                'success' => true,
                'message' => "Folder moved successfully to " . ($targetPath ?: 'root'),
                'data' => [
                    'old_path' => $sourcePath,
                    'new_path' => $destinationPath,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move folder: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rename a file
     */
    public function renameFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:media,id',
            'new_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request',
            ], 400);
        }

        try {
            $media = Media::findOrFail($request->id);

            // Keep the extension from the original file
            $extension = $media->file_extension;
            $newName = pathinfo($request->new_name, PATHINFO_FILENAME) . '.' . $extension;

            $media->original_name = $newName;
            $media->save();

            return response()->json([
                'success' => true,
                'message' => 'File renamed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to rename file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload a single file with complete field mapping
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // Max 10MB
            'path' => 'nullable|string',
            'type' => 'nullable|string|max:50', // Add type validation
            'category' => 'nullable|string|max:50', // Add category validation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded'
                ], 400);
            }

            $folderPath = $request->get('path', '');
            $disk = 'public';

            // Get original file info
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileType = explode('/', $mimeType)[0]; // 'image', 'video', 'application', etc.

            // Get type and category from request or determine automatically
            $type = $request->get('type');
            $category = $request->get('category');

            // Determine storage path based on category or file type
            $directory = 'media';
            if ($folderPath) {
                $directory .= '/' . $folderPath;
            } elseif ($category) {
                $directory .= '/' . $category;
            } elseif ($fileType === 'image') {
                $directory .= '/images';
            } elseif ($fileType === 'video') {
                $directory .= '/videos';
            } else {
                $directory .= '/documents';
            }

            // Use trait method to upload file with move
            $uploadResult = $this->uploadFileWithMove($file, $directory, null, false);

            if (!$uploadResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store file'
                ], 500);
            }

            // Generate file URL using trait method
            $fileUrl = $this->generateFileUrl($uploadResult['path'], $disk);

            // Generate thumbnail for images (optional)
            $thumbnailPath = null;
            if ($fileType === 'image' && in_array($uploadResult['extension'], ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                // You can implement thumbnail generation here
                $thumbnailPath = $this->generateThumbnail($uploadResult['path'], $disk);
            }

            // Save to database with ALL fields
            $media = Media::create([
                'file_name' => $uploadResult['filename'],
                'original_name' => $originalName,
                'file_path' => $uploadResult['path'],
                'file_url' => $fileUrl,
                'folder_path' => $folderPath,
                'mime_type' => $uploadResult['mime_type'],
                'file_extension' => $uploadResult['extension'],
                'file_size' => $uploadResult['size'],
                'disk' => $disk,
                'file_type' => $fileType,
                'type' => $type, // New field
                'category' => $category, // New field
                'thumbnail' => $thumbnailPath, // New field
                'user_id' => Auth::id(),
                'metadata' => [
                    'uploaded_by' => Auth::user() ? Auth::user()->name : 'Guest',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'upload_date' => now()->toDateTimeString(),
                    'original_mime' => $mimeType,
                    'width' => null, // Can be populated for images
                    'height' => null, // Can be populated for images
                ],
            ]);

            // Generate thumbnail after saving (if needed)
            if ($fileType === 'image' && !$thumbnailPath) {
                $media->generateThumbnail();
            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'data' => [
                    'id' => $media->id,
                    'url' => $media->full_url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'name' => $media->original_name,
                    'size' => $media->formatted_size,
                    'extension' => $media->file_extension,
                    'type' => $media->type,
                    'category' => $media->category,
                    'is_image' => $media->is_image,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Upload failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple files with complete field mapping
     */
    public function bulkUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
            'path' => 'nullable|string',
            'type' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadedFiles = [];
        $failedFiles = [];
        $folderPath = $request->get('path', '');
        $type = $request->get('type');
        $category = $request->get('category');
        $disk = 'public';

        foreach ($request->file('files') as $file) {
            try {
                $extension = $file->getClientOriginalExtension();
                $fileName = Str::random(20) . '_' . time() . '_' . uniqid() . '.' . $extension;
                $mimeType = $file->getMimeType();
                $fileType = explode('/', $mimeType)[0];

                // Determine storage path
                $directory = 'media';
                if ($folderPath) {
                    $directory .= '/' . $folderPath;
                } elseif ($category) {
                    $directory .= '/' . $category;
                } elseif ($fileType === 'image') {
                    $directory .= '/images';
                } elseif ($fileType === 'video') {
                    $directory .= '/videos';
                } else {
                    $directory .= '/documents';
                }

                // Store file using Storage facade
                $path = Storage::disk($disk)->putFileAs(
                    $directory,
                    $file,
                    $fileName
                );

                // Generate file URL with correct base URL
                $fileUrl = $this->generateFileUrl($path, $disk);

                // Generate thumbnail for images
                $thumbnailPath = null;
                if ($fileType === 'image' && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    // Implement thumbnail generation if needed
                }

                $media = Media::create([
                    'file_name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_url' => $fileUrl,
                    'folder_path' => $folderPath,
                    'mime_type' => $mimeType,
                    'file_extension' => $extension,
                    'file_size' => $file->getSize(),
                    'disk' => $disk,
                    'file_type' => $fileType,
                    'type' => $type,
                    'category' => $category,
                    'thumbnail' => $thumbnailPath,
                    'user_id' => Auth::id(),
                    'metadata' => [
                        'uploaded_by' => Auth::user() ? Auth::user()->name : 'Guest',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'upload_date' => now()->toDateTimeString(),
                        'batch_upload' => true,
                    ],
                ]);

                $uploadedFiles[] = [
                    'id' => $media->id,
                    'name' => $media->original_name,
                    'url' => $media->full_url,
                    'thumbnail_url' => $media->thumbnail_url,
                    'extension' => $media->file_extension,
                    'type' => $media->type,
                    'category' => $media->category,
                ];
            } catch (\Exception $e) {
                $failedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'uploaded' => $uploadedFiles,
            'failed' => $failedFiles,
            'message' => sprintf(
                'Uploaded: %d files, Failed: %d files',
                count($uploadedFiles),
                count($failedFiles)
            ),
        ]);
    }


    /**
     * Delete a file (single or bulk)
     */
    public function destroy(Request $request, $id = null)
    {
        try {
            // If ID is provided in route, it's a single delete
            if ($id) {
                $media = Media::findOrFail($id);

                // Delete file from storage
                Storage::disk($media->disk)->delete($media->file_path);

                // Delete from database
                $media->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully!',
                ]);
            }

            // Otherwise, it's a bulk delete from request body
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files selected for deletion',
                ], 400);
            }

            $deletedCount = 0;
            $failedCount = 0;

            foreach ($ids as $fileId) {
                try {
                    $media = Media::findOrFail($fileId);
                    Storage::disk($media->disk)->delete($media->file_path);
                    $media->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deletedCount} file(s)" . ($failedCount > 0 ? ", {$failedCount} failed" : ''),
                'deleted' => $deletedCount,
                'failed' => $failedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move file to folder
     */
    public function moveToFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|exists:media,id',
            'destination_folder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
            ], 400);
        }

        try {
            $media = Media::findOrFail($request->file_id);
            $destinationFolder = $request->destination_folder ?: '';

            // Get old file path info
            $oldPath = $media->file_path;
            $fileName = basename($oldPath);

            // Build new path
            $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $fileName;

            // Check if destination already exists
            if (Storage::disk($media->disk)->exists($newPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A file with this name already exists in the destination folder',
                ], 400);
            }

            // Move the physical file
            Storage::disk($media->disk)->move($oldPath, $newPath);

            // Update database
            $media->folder_path = $destinationFolder;
            $media->file_path = $newPath;

            // Update file URL
            $newUrl = $this->generateFileUrl($newPath, $media->disk);
            $media->file_url = $newUrl;

            $media->save();

            return response()->json([
                'success' => true,
                'message' => 'File moved successfully!',
                'data' => [
                    'id' => $media->id,
                    'new_path' => $newPath,
                    'new_folder' => $destinationFolder,
                    'new_url' => $media->file_url,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Copy file to folder
     */
    public function copyToFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_id' => 'required|exists:media,id',
            'destination_folder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
            ], 400);
        }

        try {
            $originalMedia = Media::findOrFail($request->file_id);
            $destinationFolder = $request->destination_folder ?: '';

            // Get original file info
            $originalPath = $originalMedia->file_path;
            $extension = $originalMedia->file_extension;

            // Generate unique filename for copy
            $newFileName = Str::random(20) . '_' . time() . '.' . $extension;
            $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $newFileName;

            // Copy the physical file
            Storage::disk($originalMedia->disk)->copy($originalPath, $newPath);

            // Generate new URL
            $newUrl = $this->generateFileUrl($newPath, $originalMedia->disk);

            // Create new database record
            $newMedia = $originalMedia->replicate();
            $newMedia->file_name = $newFileName;
            $newMedia->file_path = $newPath;
            $newMedia->file_url = $newUrl;
            $newMedia->folder_path = $destinationFolder;
            $newMedia->user_id = Auth::id();
            $newMedia->created_at = now();
            $newMedia->updated_at = now();

            // Update metadata
            $metadata = $originalMedia->metadata ?: [];
            $metadata['copied_from'] = $originalMedia->id;
            $metadata['copied_at'] = now()->toDateTimeString();
            $metadata['copied_by'] = Auth::user() ? Auth::user()->name : 'Unknown';
            $newMedia->metadata = $metadata;

            $newMedia->save();

            return response()->json([
                'success' => true,
                'message' => 'File copied successfully!',
                'data' => [
                    'id' => $newMedia->id,
                    'name' => $newMedia->original_name,
                    'path' => $newPath,
                    'folder' => $destinationFolder,
                    'url' => $newMedia->file_url,
                    'size' => $newMedia->formatted_size,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get folder list for selection
     */
    public function getFolders(Request $request)
    {
        $currentPath = $request->get('exclude_path', '');
        $basePath = storage_path('app/public/media');

        try {
            // Use trait method to get folder tree
            $folders = $this->getFolderTree($basePath, '', $currentPath);

            return response()->json([
                'success' => true,
                'folders' => $folders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load folders: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk move files to folder
     */
    public function bulkMoveToFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'exists:media,id',
            'destination_folder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $validator->errors(),
            ], 400);
        }

        $destinationFolder = $request->destination_folder ?: '';
        $fileIds = $request->file_ids;
        $successCount = 0;
        $failedFiles = [];

        try {
            foreach ($fileIds as $fileId) {
                try {
                    $media = Media::findOrFail($fileId);

                    // Get old file path info
                    $oldPath = $media->file_path;
                    $fileName = basename($oldPath);

                    // Build new path
                    $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $fileName;

                    // Check if destination already exists
                    if (Storage::disk($media->disk)->exists($newPath)) {
                        $failedFiles[] = [
                            'id' => $fileId,
                            'name' => $media->original_name,
                            'error' => 'File already exists in destination folder',
                        ];

                        continue;
                    }

                    // Move the physical file
                    Storage::disk($media->disk)->move($oldPath, $newPath);

                    // Update database
                    $media->folder_path = $destinationFolder;
                    $media->file_path = $newPath;

                    // Update file URL
                    $newUrl = $this->generateFileUrl($newPath, $media->disk);
                    $media->file_url = $newUrl;

                    $media->save();
                    $successCount++;
                } catch (\Exception $e) {
                    $media = Media::find($fileId);
                    $failedFiles[] = [
                        'id' => $fileId,
                        'name' => $media ? $media->original_name : 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $folderName = $destinationFolder ?: 'Root';
            $message = "Moved {$successCount} file(s) to \"{$folderName}\"";
            if (! empty($failedFiles)) {
                $message .= '. ' . count($failedFiles) . ' file(s) failed to move.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => count($failedFiles),
                    'failed_files' => $failedFiles,
                    'destination_folder' => $destinationFolder,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move files: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk copy files to folder
     */
    public function bulkCopyToFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'exists:media,id',
            'destination_folder' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $validator->errors(),
            ], 400);
        }

        $destinationFolder = $request->destination_folder ?: '';
        $fileIds = $request->file_ids;
        $successCount = 0;
        $failedFiles = [];
        $copiedFiles = [];

        try {
            foreach ($fileIds as $fileId) {
                try {
                    $originalMedia = Media::findOrFail($fileId);

                    // Get original file info
                    $originalPath = $originalMedia->file_path;
                    $extension = $originalMedia->file_extension;

                    // Generate unique filename for copy
                    $newFileName = Str::random(20) . '_' . time() . '_' . uniqid() . '.' . $extension;
                    $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $newFileName;

                    // Copy the physical file
                    Storage::disk($originalMedia->disk)->copy($originalPath, $newPath);

                    // Generate new URL
                    $newUrl = $this->generateFileUrl($newPath, $originalMedia->disk);

                    // Create new database record
                    $newMedia = $originalMedia->replicate();
                    $newMedia->file_name = $newFileName;
                    $newMedia->file_path = $newPath;
                    $newMedia->file_url = $newUrl;
                    $newMedia->folder_path = $destinationFolder;
                    $newMedia->user_id = Auth::id();
                    $newMedia->created_at = now();
                    $newMedia->updated_at = now();

                    // Update metadata
                    $metadata = $originalMedia->metadata ?: [];
                    $metadata['copied_from'] = $originalMedia->id;
                    $metadata['copied_at'] = now()->toDateTimeString();
                    $metadata['copied_by'] = Auth::user() ? Auth::user()->name : 'Unknown';
                    $newMedia->metadata = $metadata;

                    $newMedia->save();
                    $successCount++;

                    $copiedFiles[] = [
                        'id' => $newMedia->id,
                        'name' => $newMedia->original_name,
                        'url' => $newMedia->file_url,
                    ];
                } catch (\Exception $e) {
                    $media = Media::find($fileId);
                    $failedFiles[] = [
                        'id' => $fileId,
                        'name' => $media ? $media->original_name : 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $folderName = $destinationFolder ?: 'Root';
            $message = "Copied {$successCount} file(s) to \"{$folderName}\"";
            if (! empty($failedFiles)) {
                $message .= '. ' . count($failedFiles) . ' file(s) failed to copy.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'success_count' => $successCount,
                    'failed_count' => count($failedFiles),
                    'failed_files' => $failedFiles,
                    'copied_files' => $copiedFiles,
                    'destination_folder' => $destinationFolder,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to copy files: ' . $e->getMessage(),
            ], 500);
        }
    }
}
