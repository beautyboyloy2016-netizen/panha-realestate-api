<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

trait ImageUploadTrait
{
  public $defaultAllowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  public $defaultMaxImageSizeKB = 5 * 1024 * 1024; // 5MB
  public $uploadedPath = '';

  public function __construct()
  {
    $this->uploadedPath = storage_path('app/public');
    if (!File::exists($this->uploadedPath)) {
      File::makeDirectory($this->uploadedPath, 0755, true);
    }
  }

  /**
   * Upload a single image
   *
   * @param Request $request
   * @param string $inputName
   * @param string $path
   * @param string $filename
   * @param string $disk
   * @param array $allowedTypes
   * @param int $maxSize
   * @return string|null
   */
  public function uploadImage(
    Request $request,
    $inputName,
    $path,
    $filename,
    $disk = 'public',
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    $maxSize = 2048
  ) {
    if ($request->hasFile($inputName)) {
      try {
        $file = $request->file($inputName);

        // Validate file
        $this->validateFile($file, $allowedTypes, $maxSize);

        // Generate unique filename
        $generatedFilename = $this->generateFilename($filename, $file->getClientOriginalExtension());

        // Store file using Storage facade
        $file->move($this->uploadedPath . $path, $generatedFilename);

        $filePath = $path . '/' . $generatedFilename;

        Log::info('Image uploaded successfully', [
          'filename' => $generatedFilename,
          'path' => $filePath,
          'disk' => $disk
        ]);

        return $filePath;
      } catch (Exception $e) {
        Log::error('Image upload failed: ' . $e->getMessage(), [
          'input' => $inputName,
          'path' => $path
        ]);
        return null;
      }
    }
    return null;
  }

  /**
   * Upload multiple images
   *
   * @param Request $request
   * @param string $inputName
   * @param string $path
   * @param string $filename
   * @param string $disk
   * @param array $allowedTypes
   * @param int $maxSize
   * @return array|null
   */
  public function uploadMultiImages(
    Request $request,
    $inputName,
    $path,
    $filename,
    $disk = 'public',
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    $maxSize = 2048
  ) {
    if ($request->hasFile($inputName)) {
      try {
        $files = $request->file($inputName);
        $filePaths = [];

        foreach ($files as $file) {
          // Validate file
          $this->validateFile($file, $allowedTypes, $maxSize);

          // Generate unique filename
          $generatedFilename = $this->generateFilename($filename, $file->getClientOriginalExtension());

          // Store file using Storage facade
          // $filePath = $file->storeAs($path, $generatedFilename, $disk);
          $file->move($this->uploadedPath . $path, $generatedFilename);
          $filePaths[] = $path . '/' . $generatedFilename;
        }

        Log::info('Multiple images uploaded successfully', [
          'count' => \count($filePaths),
          'disk' => $disk
        ]);

        return $filePaths;
      } catch (Exception $e) {
        Log::error('Multiple image upload failed: ' . $e->getMessage());
        return null;
      }
    }
    return null;
  }

  /**
   * Update an image
   *
   * @param Request $request
   * @param string $inputName
   * @param string $path
   * @param string $filename
   * @param string|null $oldPath Full path to old file (e.g., 'uploads/image.jpg')
   * @param string $disk
   * @param array $allowedTypes
   * @param int $maxSize
   * @return string|null Returns only the filename (not full path)
   */
  public function updateImage(
    Request $request,
    $inputName,
    $path,
    $filename,
    $oldPath = null,
    $disk = 'public',
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    $maxSize = 2048
  ) {
    if ($request->hasFile($inputName)) {
      try {
        // Delete old file if exists (oldPath should be full path like 'uploads/image.jpg')
        if ($oldPath && Storage::disk($disk)->exists($oldPath)) {
          Storage::disk($disk)->delete($oldPath);
          Log::info('Old image deleted during update', ['path' => $oldPath]);
        }

        $file = $request->file($inputName);

        // Validate file
        $this->validateFile($file, $allowedTypes, $maxSize);

        // Generate unique filename
        $generatedFilename = $this->generateFilename($filename, $file->getClientOriginalExtension());

        // Store file using Storage facade
        $file->move($this->uploadedPath . $path, $generatedFilename);
        $filePath = $this->uploadedPath . $path . '/' . $generatedFilename;

        Log::info('Image updated successfully', [
          'old_path' => $oldPath,
          'new_path' => $filePath,
          'new_filename' => $generatedFilename
        ]);

        return basename($filePath);
      } catch (Exception $e) {
        Log::error('Image update failed: ' . $e->getMessage());
        return null;
      }
    }

    // Return old filename if no new file was uploaded
    return $oldPath ? $oldPath : null;
  }

  /**
   * Delete an image
   *
   * @param string|null $path
   * @param string $disk
   * @return bool
   */
  public function deleteImage($path, $disk = 'public')
  {
    if (empty($path)) {
      return false;
    }

    try {
      if (Storage::disk($disk)->exists($path)) {
        $result = Storage::disk($disk)->delete($path);

        if ($result) {
          Log::info('Image deleted successfully', ['path' => $path]);
        }

        return $result;
      }
      return false;
    } catch (Exception $e) {
      Log::error('Image deletion failed: ' . $e->getMessage(), ['path' => $path]);
      return false;
    }
  }

  /**
   * Delete multiple images
   *
   * @param array $paths
   * @param string $disk
   * @return int Number of successfully deleted files
   */
  public function deleteMultipleImages(array $paths, $disk = 'public')
  {
    $deletedCount = 0;

    foreach ($paths as $path) {
      if (Storage::disk($disk)->exists($path)) {
        if (Storage::disk($disk)->delete($path)) {
          $deletedCount++;
        }
      }
    }

    Log::info('Multiple images deleted', [
      'requested' => \count($paths),
      'deleted' => $deletedCount
    ]);

    return $deletedCount;
  }

  /**
   * Get public URL for an image
   *
   * @param string $path
   * @param string $disk
   * @return string|null
   */
  public function getImageUrl($path, $disk = 'public')
  {
    if (!$path) {
      return null;
    }

    if (Storage::disk($disk)->exists($path)) {
      return Storage::disk($disk)->url($path);
    }

    return null;
  }

  /**
   * Get public URLs for multiple images
   *
   * @param array $paths
   * @param string $disk
   * @param bool $skipNonExistent
   * @return array
   */
  public function getAllImagesUrl(array $paths, $disk = 'public', $skipNonExistent = false)
  {
    $urls = [];

    foreach ($paths as $key => $path) {
      if (!$path) {
        if (!$skipNonExistent) {
          $urls[$key] = null;
        }
        continue;
      }

      if (Storage::disk($disk)->exists($path)) {
        $urls[$key] = Storage::disk($disk)->url($path);
      } else {
        if (!$skipNonExistent) {
          $urls[$key] = null;
        }
      }
    }

    return $urls;
  }

  /**
   * Check if an image exists
   *
   * @param string $path
   * @param string $disk
   * @return bool
   */
  public function imageExists($path, $disk = 'public')
  {
    if (!$path) {
      return false;
    }

    return Storage::disk($disk)->exists($path);
  }

  /**
   * Check if multiple images exist
   *
   * @param array $paths
   * @param string $disk
   * @return array
   */
  public function multipleImagesExist(array $paths, $disk = 'public')
  {
    $results = [];

    foreach ($paths as $key => $path) {
      $results[$key] = $this->imageExists($path, $disk);
    }

    return $results;
  }

  /**
   * Get image file size
   *
   * @param string $path
   * @param string $disk
   * @return int|null Size in bytes
   */
  public function getImageSize($path, $disk = 'public')
  {
    if (Storage::disk($disk)->exists($path)) {
      return Storage::disk($disk)->size($path);
    }

    return null;
  }

  /**
   * Get human-readable file size
   *
   * @param string $path
   * @param string $disk
   * @return string|null
   */
  public function getImageSizeFormatted($path, $disk = 'public')
  {
    $bytes = $this->getImageSize($path, $disk);

    if ($bytes === null) {
      return null;
    }

    return $this->formatBytes($bytes);
  }

  /**
   * Get image metadata
   *
   * @param string $path
   * @param string $disk
   * @return array|null
   */
  public function getImageMetadata($path, $disk = 'public')
  {
    if (!Storage::disk($disk)->exists($path)) {
      return null;
    }

    try {
      $fullPath = Storage::disk($disk)->path($path);

      if (file_exists($fullPath) && is_file($fullPath)) {
        $imageInfo = @getimagesize($fullPath);

        if ($imageInfo !== false) {
          return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime' => $imageInfo['mime'],
            'size' => Storage::disk($disk)->size($path),
            'size_formatted' => $this->getImageSizeFormatted($path, $disk),
            'last_modified' => Storage::disk($disk)->lastModified($path),
            'url' => Storage::disk($disk)->url($path)
          ];
        }
      }
    } catch (Exception $e) {
      Log::error('Failed to get image metadata: ' . $e->getMessage());
    }

    return null;
  }

  /**
   * Copy image to another location
   *
   * @param string $from
   * @param string $to
   * @param string $disk
   * @return bool
   */
  public function copyImage($from, $to, $disk = 'public')
  {
    try {
      if (Storage::disk($disk)->exists($from)) {
        return Storage::disk($disk)->copy($from, $to);
      }
    } catch (Exception $e) {
      Log::error('Image copy failed: ' . $e->getMessage());
    }

    return false;
  }

  /**
   * Move image to another location
   *
   * @param string $from
   * @param string $to
   * @param string $disk
   * @return bool
   */
  public function moveImage($from, $to, $disk = 'public')
  {
    try {
      if (Storage::disk($disk)->exists($from)) {
        return Storage::disk($disk)->move($from, $to);
      }
    } catch (Exception $e) {
      Log::error('Image move failed: ' . $e->getMessage());
    }

    return false;
  }

  /**
   * Validate file
   *
   * @param \Illuminate\Http\UploadedFile $file
   * @param array $allowedTypes
   * @param int $maxSize
   * @return void
   * @throws \Exception
   */
  protected function validateFile($file, $allowedTypes, $maxSize)
  {
    $ext = strtolower($file->getClientOriginalExtension());
    $mimeType = $file->getMimeType();

    // Map of allowed extensions to MIME types
    $mimeMap = [
      'jpg' => ['image/jpeg'],
      'jpeg' => ['image/jpeg'],
      'png' => ['image/png'],
      'gif' => ['image/gif'],
      'webp' => ['image/webp']
    ];

    // Check file extension
    if (!\in_array($ext, $allowedTypes)) {
      throw new Exception("File type not allowed. Allowed types: " . implode(', ', $allowedTypes));
    }

    // Validate MIME type for security
    if (isset($mimeMap[$ext]) && !\in_array($mimeType, $mimeMap[$ext])) {
      throw new Exception("Invalid MIME type for {$ext} file. Expected: " . implode(', ', $mimeMap[$ext]) . ", got: {$mimeType}");
    }

    // Check file size
    if ($file->getSize() > $maxSize * 1024) {
      throw new Exception("File size exceeds maximum allowed size of {$maxSize}KB");
    }

    // Additional security check for image validity
    if (!$this->isValidImage($file)) {
      throw new Exception("Invalid image file");
    }
  }

  /**
   * Check if file is a valid image
   *
   * @param \Illuminate\Http\UploadedFile $file
   * @return bool
   */
  protected function isValidImage($file)
  {
    try {
      // Try multiple methods to get the file path
      $filePath = $file->getPathname() ?: $file->getRealPath() ?: $file->path();

      Log::info('Validating image file', [
        'original_name' => $file->getClientOriginalName(),
        'getPathname' => $file->getPathname(),
        'getRealPath' => $file->getRealPath(),
        'path' => method_exists($file, 'path') ? $file->path() : 'N/A',
        'file_path' => $filePath,
        'file_exists' => file_exists($filePath),
        'is_readable' => is_readable($filePath)
      ]);

      if (empty($filePath) || !file_exists($filePath)) {
        Log::error('File path is empty or does not exist');
        return false;
      }

      $image = @getimagesize($filePath);

      Log::info('getimagesize result', [
        'result' => $image !== false ? 'valid' : 'invalid',
        'details' => $image
      ]);

      return $image !== false;
    } catch (Exception $e) {
      Log::error('isValidImage exception: ' . $e->getMessage());
      return false;
    }
  }
  /**
   * Generate unique filename
   *
   * @param string $prefix
   * @param string $extension
   * @return string
   */
  protected function generateFilename($prefix, $extension)
  {
    // Remove any special characters from prefix
    $prefix = preg_replace('/[^A-Za-z0-9\-_]/', '', $prefix);

    // Generate unique identifier with timestamp
    $uniqueId = time() . '_' . uniqid();

    return $prefix . '_' . $uniqueId . '.' . strtolower($extension);
  }

  /**
   * Format bytes to human-readable format
   *
   * @param int $bytes
   * @param int $precision
   * @return string
   */
  protected function formatBytes($bytes, $precision = 2)
  {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < \count($units) - 1; $i++) {
      $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
  }

  /**
   * Get all images from a directory
   *
   * @param string $directory
   * @param string $disk
   * @param array $allowedTypes
   * @return array
   */
  public function getImagesFromDirectory($directory, $disk = 'public', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'])
  {
    try {
      $files = Storage::disk($disk)->files($directory);
      $images = [];

      foreach ($files as $file) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (\in_array($extension, $allowedTypes)) {
          $images[] = [
            'path' => $file,
            'filename' => basename($file),
            'url' => Storage::disk($disk)->url($file),
            'size' => Storage::disk($disk)->size($file),
            'size_formatted' => $this->formatBytes(Storage::disk($disk)->size($file)),
            'last_modified' => Storage::disk($disk)->lastModified($file)
          ];
        }
      }

      return $images;
    } catch (Exception $e) {
      Log::error('Failed to get images from directory: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * Create image directory if it doesn't exist
   *
   * @param string $directory The directory path to create
   * @param string $disk
   * @param bool $recursive Create nested directories
   * @return bool
   */
  public function ensureDirectoryExists($directory, $disk = 'public', $recursive = true)
  {
    try {
      if (!Storage::disk($disk)->exists($directory)) {
        return Storage::disk($disk)->makeDirectory($directory, 0755, $recursive);
      }

      return true;
    } catch (Exception $e) {
      Log::error('Failed to create directory: ' . $e->getMessage(), ['directory' => $directory]);
      return false;
    }
  }

  /**
   * Create directory from file path if it doesn't exist
   *
   * @param string $filePath The file path (directory will be extracted)
   * @param string $disk
   * @return bool
   */
  public function ensureFileDirectoryExists($filePath, $disk = 'public')
  {
    $directory = dirname($filePath);
    return $this->ensureDirectoryExists($directory, $disk);
  }

  /**
   * Ensure default media folders exist (images, videos, documents)
   *
   * @param string $baseMediaPath Base path for media storage (e.g., 'media' or full system path)
   * @param bool $useFullPath Whether the baseMediaPath is a full system path
   * @return bool
   */
  public function ensureDefaultMediaFoldersExist($baseMediaPath = 'media', $useFullPath = false)
  {
    try {
      // If using full path, work directly with filesystem
      if ($useFullPath) {
        if (!\Illuminate\Support\Facades\File::exists($baseMediaPath)) {
          \Illuminate\Support\Facades\File::makeDirectory($baseMediaPath, 0755, true);
        }

        // Ensure default folders exist (images, videos, documents)
        $defaultFolders = ['images', 'videos', 'documents'];
        foreach ($defaultFolders as $folder) {
          $folderPath = $baseMediaPath . '/' . $folder;
          if (!\Illuminate\Support\Facades\File::exists($folderPath)) {
            \Illuminate\Support\Facades\File::makeDirectory($folderPath, 0755, true);
          }
        }

        Log::info('Default media folders ensured', [
          'base_path' => $baseMediaPath,
          'folders' => $defaultFolders
        ]);

        return true;
      }

      // Using Storage facade with disk
      $disk = 'public';
      if (!Storage::disk($disk)->exists($baseMediaPath)) {
        Storage::disk($disk)->makeDirectory($baseMediaPath, 0755, true);
      }

      // Ensure default folders exist
      $defaultFolders = ['images', 'videos', 'documents'];
      foreach ($defaultFolders as $folder) {
        $folderPath = $baseMediaPath . '/' . $folder;
        if (!Storage::disk($disk)->exists($folderPath)) {
          Storage::disk($disk)->makeDirectory($folderPath, 0755, true);
        }
      }

      Log::info('Default media folders ensured', [
        'base_path' => $baseMediaPath,
        'folders' => $defaultFolders,
        'disk' => $disk
      ]);

      return true;
    } catch (Exception $e) {
      Log::error('Failed to ensure default media folders: ' . $e->getMessage(), [
        'base_path' => $baseMediaPath
      ]);
      return false;
    }
  }

  /**
   * Upload file with move method (most reliable for large files)
   *
   * @param \Illuminate\Http\UploadedFile $file Uploaded file object
   * @param string $folderPath Folder path relative to storage/app/public (e.g., 'media/images')
   * @param string|null $customFileName Custom filename (optional, auto-generated if null)
   * @param bool $useFullPath Whether folderPath is absolute system path
   * @return array|null Returns ['path' => ..., 'filename' => ..., 'size' => ..., 'extension' => ...] or null on failure
   */
  public function uploadFileWithMove($file, $folderPath = 'media', $customFileName = null, $useFullPath = false)
  {
    try {
      // Get file info BEFORE moving
      $extension = $file->getClientOriginalExtension();
      $fileName = $customFileName ?: Str::random(20) . '_' . time() . '.' . $extension;
      $fileSize = $file->getSize();
      $mimeType = $file->getMimeType();

      // Determine full storage path
      if ($useFullPath) {
        $fullStoragePath = $folderPath . '/' . $fileName;
        $dirPath = $folderPath;
      } else {
        $fullStoragePath = storage_path('app/public/' . $folderPath . '/' . $fileName);
        $dirPath = storage_path('app/public/' . $folderPath);
      }

      // Ensure directory exists
      if (!file_exists($dirPath)) {
        mkdir($dirPath, 0755, true);
      }

      // Move the uploaded file
      if (!$file->move($dirPath, $fileName)) {
        Log::error('Failed to move file', ['filename' => $fileName]);
        return null;
      }

      Log::info('File uploaded successfully with move method', [
        'filename' => $fileName,
        'path' => $fullStoragePath,
        'size' => $fileSize
      ]);

      return [
        'path' => $useFullPath ? $fullStoragePath : $folderPath . '/' . $fileName,
        'filename' => $fileName,
        'size' => $fileSize,
        'extension' => $extension,
        'mime_type' => $mimeType,
      ];
    } catch (Exception $e) {
      Log::error('File upload with move failed: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Get files and folders from a directory (reusable for any controller)
   *
   * @param string $basePath Base storage path (e.g., storage_path('app/public/media'))
   * @param string $relativePath Relative path from base (e.g., 'images' or 'images/2024')
   * @param bool $includeFiles Include files in response
   * @param bool $includeFolders Include folders in response
   * @param array $allowedExtensions Filter files by extension (empty = all)
   * @return array ['folders' => [...], 'files' => [...]]
   */
  public function getDirectoryContents(
    $basePath,
    $relativePath = '',
    $includeFiles = true,
    $includeFolders = true,
    $allowedExtensions = []
  ) {
    // Normalize the relative path - remove leading/trailing slashes
    $relativePath = trim($relativePath, '/');

    $fullPath = $basePath . ($relativePath ? '/' . $relativePath : '');
    $folders = [];
    $files = [];

    try {
      // Ensure path exists
      if (!File::exists($fullPath)) {
        File::makeDirectory($fullPath, 0755, true);
      }

      // Get folders
      if ($includeFolders && File::exists($fullPath)) {
        $directories = File::directories($fullPath);
        foreach ($directories as $dir) {
          $folderName = basename($dir);
          $folders[] = [
            'name' => $folderName,
            'path' => $relativePath ? $relativePath . '/' . $folderName : $folderName,
            'full_path' => $dir,
            'modified' => date('Y-m-d H:i:s', filemtime($dir)),
            'size' => $this->getFolderSize($dir),
            'file_count' => \count(File::files($dir)),
            'type' => 'folder',
          ];
        }
      }

      // Get files
      if ($includeFiles && File::exists($fullPath)) {
        $fileList = File::files($fullPath);
        foreach ($fileList as $fileItem) {
          $ext = strtolower($fileItem->getExtension());

          // Filter by extension if specified
          if (!empty($allowedExtensions) && !\in_array($ext, $allowedExtensions)) {
            continue;
          }

          $files[] = [
            'name' => $fileItem->getFilename(),
            'path' => $relativePath ? $relativePath . '/' . $fileItem->getFilename() : $fileItem->getFilename(),
            'full_path' => $fileItem->getPathname(),
            'extension' => $ext,
            'size' => $fileItem->getSize(),
            'size_formatted' => $this->formatBytes($fileItem->getSize()),
            'modified' => date('Y-m-d H:i:s', $fileItem->getMTime()),
            'mime_type' => mime_content_type($fileItem->getPathname()),
            'type' => 'file',
          ];
        }
      }

      Log::info('Directory contents retrieved', [
        'path' => $fullPath,
        'folders' => \count($folders),
        'files' => \count($files)
      ]);

      return [
        'folders' => $folders,
        'files' => $files,
      ];
    } catch (Exception $e) {
      Log::error('Failed to get directory contents: ' . $e->getMessage(), [
        'path' => $fullPath
      ]);
      return [
        'folders' => [],
        'files' => [],
      ];
    }
  }

  /**
   * Get folder size recursively
   *
   * @param string $path Full path to folder
   * @return string Formatted size (e.g., "1.5 MB")
   */
  public function getFolderSize($path)
  {
    $size = 0;
    try {
      foreach (File::allFiles($path) as $file) {
        $size += $file->getSize();
      }
    } catch (Exception $e) {
      Log::error('Failed to get folder size: ' . $e->getMessage());
    }

    return $this->formatBytes($size);
  }

  /**
   * Create a folder (reusable method)
   *
   * @param string $basePath Base storage path
   * @param string $folderName New folder name
   * @param string $relativePath Parent folder path (optional)
   * @return array ['success' => bool, 'message' => string, 'path' => string]
   */
  public function createFolderInPath($basePath, $folderName, $relativePath = '')
  {
    try {
      // Validate folder name
      if (!preg_match('/^[a-zA-Z0-9_-]+$/', $folderName)) {
        return [
          'success' => false,
          'message' => 'Invalid folder name. Use only letters, numbers, hyphens, and underscores.',
        ];
      }

      // Ensure base path exists
      if (!File::exists($basePath)) {
        File::makeDirectory($basePath, 0755, true);
      }

      // Build full folder path
      $newFolderPath = $relativePath ? $relativePath . '/' . $folderName : $folderName;
      $fullPath = $basePath . '/' . $newFolderPath;

      Log::info('Attempting to create folder', [
        'base_path' => $basePath,
        'folder_name' => $folderName,
        'relative_path' => $relativePath,
        'new_folder_path' => $newFolderPath,
        'full_path' => $fullPath,
      ]);

      if (File::exists($fullPath)) {
        return [
          'success' => false,
          'message' => 'Folder already exists',
          'path' => $newFolderPath,
        ];
      }

      // Create the directory
      $result = File::makeDirectory($fullPath, 0755, true);

      if (!$result) {
        Log::error('File::makeDirectory returned false', ['path' => $fullPath]);
        return [
          'success' => false,
          'message' => 'Failed to create directory',
        ];
      }

      Log::info('Folder created successfully', [
        'path' => $fullPath,
        'relative_path' => $newFolderPath
      ]);

      return [
        'success' => true,
        'message' => 'Folder created successfully',
        'path' => $newFolderPath,
      ];
    } catch (Exception $e) {
      Log::error('Failed to create folder: ' . $e->getMessage(), [
        'exception' => $e->getTraceAsString(),
        'base_path' => $basePath,
        'folder_name' => $folderName,
      ]);
      return [
        'success' => false,
        'message' => 'Failed to create folder: ' . $e->getMessage(),
      ];
    }
  }

  /**
   * Rename a folder (reusable method)
   *
   * @param string $basePath Base storage path
   * @param string $oldPath Old folder path (relative)
   * @param string $newName New folder name
   * @return array ['success' => bool, 'message' => string, 'path' => string]
   */
  public function renameFolderInPath($basePath, $oldPath, $newName)
  {
    try {
      // Validate new name
      if (!preg_match('/^[a-zA-Z0-9_-]+$/', $newName)) {
        return [
          'success' => false,
          'message' => 'Invalid folder name',
        ];
      }

      $oldFullPath = $basePath . '/' . $oldPath;
      $parentPath = dirname($oldFullPath);
      $newFullPath = $parentPath . '/' . $newName;

      if (!File::exists($oldFullPath)) {
        return [
          'success' => false,
          'message' => 'Folder not found',
        ];
      }

      if (File::exists($newFullPath)) {
        return [
          'success' => false,
          'message' => 'A folder with this name already exists',
        ];
      }

      File::move($oldFullPath, $newFullPath);

      $newRelativePath = str_replace($basePath . '/', '', $newFullPath);

      Log::info('Folder renamed successfully', [
        'old' => $oldFullPath,
        'new' => $newFullPath
      ]);

      return [
        'success' => true,
        'message' => 'Folder renamed successfully',
        'path' => $newRelativePath,
      ];
    } catch (Exception $e) {
      Log::error('Failed to rename folder: ' . $e->getMessage());
      return [
        'success' => false,
        'message' => 'Failed to rename folder: ' . $e->getMessage(),
      ];
    }
  }

  /**
   * Delete a folder (reusable method)
   *
   * @param string $basePath Base storage path
   * @param string $relativePath Folder path to delete (relative)
   * @param bool $forceDelete Delete even if not empty
   * @return array ['success' => bool, 'message' => string]
   */
  public function deleteFolderInPath($basePath, $relativePath, $forceDelete = false)
  {
    try {
      $fullPath = $basePath . '/' . $relativePath;

      if (!File::exists($fullPath)) {
        return [
          'success' => false,
          'message' => 'Folder not found',
        ];
      }

      // Check if folder is empty
      if (!$forceDelete) {
        $files = File::files($fullPath);
        $dirs = File::directories($fullPath);

        if (\count($files) > 0 || \count($dirs) > 0) {
          return [
            'success' => false,
            'message' => 'Cannot delete non-empty folder',
          ];
        }
      }

      File::deleteDirectory($fullPath);

      Log::info('Folder deleted successfully', ['path' => $fullPath]);

      return [
        'success' => true,
        'message' => 'Folder deleted successfully',
      ];
    } catch (Exception $e) {
      Log::error('Failed to delete folder: ' . $e->getMessage());
      return [
        'success' => false,
        'message' => 'Failed to delete folder: ' . $e->getMessage(),
      ];
    }
  }

  /**
   * Get folder tree recursively (for dropdowns, navigation, etc.)
   *
   * @param string $basePath Base storage path
   * @param string $currentPath Current path being processed (for recursion)
   * @param string $excludePath Path to exclude (e.g., for move operations)
   * @param int $maxDepth Maximum depth to traverse (-1 = unlimited)
   * @param int $currentDepth Current depth (for recursion)
   * @return array Array of folders with name, path, and level
   */
  public function getFolderTree(
    $basePath,
    $currentPath = '',
    $excludePath = '',
    $maxDepth = -1,
    $currentDepth = 0
  ) {
    $folders = [];

    // Add root if this is the first call
    if ($currentDepth === 0) {
      $folders[] = [
        'name' => 'Root',
        'path' => '',
        'level' => 0,
      ];
    }

    // Check depth limit
    if ($maxDepth !== -1 && $currentDepth >= $maxDepth) {
      return $folders;
    }

    $fullPath = $basePath . ($currentPath ? '/' . $currentPath : '');

    try {
      if (File::exists($fullPath)) {
        $directories = File::directories($fullPath);

        foreach ($directories as $dir) {
          $folderName = basename($dir);
          $folderPath = $currentPath ? $currentPath . '/' . $folderName : $folderName;

          // Skip excluded path
          if ($excludePath && $folderPath === $excludePath) {
            continue;
          }

          $folders[] = [
            'name' => $folderName,
            'path' => $folderPath,
            'level' => $currentDepth + 1,
          ];

          // Get subfolders recursively
          $subfolders = $this->getFolderTree(
            $basePath,
            $folderPath,
            $excludePath,
            $maxDepth,
            $currentDepth + 1
          );

          // Remove root from subfolders
          if ($currentDepth === 0 && !empty($subfolders)) {
            \array_shift($subfolders);
          }

          $folders = \array_merge($folders, $subfolders);
        }
      }
    } catch (Exception $e) {
      Log::error('Failed to get folder tree: ' . $e->getMessage());
    }

    return $folders;
  }

  /**
   * Generate file URL from path
   *
   * @param string $filePath File path relative to storage/app/public (e.g., 'media/image.jpg')
   * @param string $disk Disk name
   * @return string File URL
   */
  public function generateFileUrl($filePath, $disk = 'public')
  {
    try {
      $fileUrl = Storage::disk($disk)->url($filePath);

      // Ensure URL uses correct base URL
      if (!filter_var($fileUrl, FILTER_VALIDATE_URL)) {
        $fileUrl = config('app.url') . (str_starts_with($fileUrl, '/') ? '' : '/') . $fileUrl;
      } else {
        // Check if URL has correct port for development
        $currentUrl = config('app.url');
        $urlParts = parse_url($fileUrl);
        $configParts = parse_url($currentUrl);

        if (
          isset($configParts['port']) &&
          (!isset($urlParts['port']) || $urlParts['port'] != $configParts['port'])
        ) {
          $correctUrl = $configParts['scheme'] . '://' . $configParts['host'];
          if (isset($configParts['port'])) {
            $correctUrl .= ':' . $configParts['port'];
          }
          $correctUrl .= $urlParts['path'];
          $fileUrl = $correctUrl;
        }
      }

      return $fileUrl;
    } catch (Exception $e) {
      Log::error('Failed to generate file URL: ' . $e->getMessage());
      return '';
    }
  }

  /**
   * Generate thumbnail for an image
   *
   * @param string $sourcePath Source image path
   * @param string $disk Storage disk
   * @param int $width Thumbnail width
   * @param int $height Thumbnail height
   * @return string|null Thumbnail path or null on failure
   */
  public function generateThumbnail($sourcePath, $disk = 'public', $width = 300, $height = 300)
  {
    try {
      // Check if source exists
      if (!Storage::disk($disk)->exists($sourcePath)) {
        return null;
      }

      // Get source file info
      $sourceFullPath = Storage::disk($disk)->path($sourcePath);
      $pathInfo = pathinfo($sourcePath);
      $thumbnailName = $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
      $thumbnailDir = 'thumbnails/' . dirname($sourcePath);
      $thumbnailPath = $thumbnailDir . '/' . $thumbnailName;

      // Ensure thumbnail directory exists
      Storage::disk($disk)->makeDirectory($thumbnailDir);

      // Generate thumbnail using GD or Imagick
      // This is a basic implementation using GD
      $sourceImage = null;
      $extension = strtolower($pathInfo['extension']);

      switch ($extension) {
        case 'jpg':
        case 'jpeg':
          $sourceImage = imagecreatefromjpeg($sourceFullPath);
          break;
        case 'png':
          $sourceImage = imagecreatefrompng($sourceFullPath);
          break;
        case 'gif':
          $sourceImage = imagecreatefromgif($sourceFullPath);
          break;
        case 'webp':
          $sourceImage = imagecreatefromwebp($sourceFullPath);
          break;
        default:
          return null;
      }

      if (!$sourceImage) {
        return null;
      }

      // Get original dimensions
      $origWidth = imagesx($sourceImage);
      $origHeight = imagesy($sourceImage);

      // Calculate new dimensions maintaining aspect ratio
      $ratio = \min($width / $origWidth, $height / $origHeight);
      $newWidth = (int)($origWidth * $ratio);
      $newHeight = (int)($origHeight * $ratio);

      // Create thumbnail
      $thumbnail = imagecreatetruecolor($newWidth, $newHeight);

      // Preserve transparency for PNG and WebP
      if (\in_array($extension, ['png', 'webp'])) {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
      }

      // Resize image
      imagecopyresampled(
        $thumbnail,
        $sourceImage,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $origWidth,
        $origHeight
      );

      // Save thumbnail
      $thumbnailFullPath = Storage::disk($disk)->path($thumbnailPath);
      $saved = false;

      switch ($extension) {
        case 'jpg':
        case 'jpeg':
          $saved = imagejpeg($thumbnail, $thumbnailFullPath, 85);
          break;
        case 'png':
          $saved = imagepng($thumbnail, $thumbnailFullPath, 8);
          break;
        case 'gif':
          $saved = imagegif($thumbnail, $thumbnailFullPath);
          break;
        case 'webp':
          $saved = imagewebp($thumbnail, $thumbnailFullPath, 85);
          break;
      }

      // Clean up
      imagedestroy($sourceImage);
      imagedestroy($thumbnail);

      if ($saved) {
        Log::info('Thumbnail generated successfully', [
          'source' => $sourcePath,
          'thumbnail' => $thumbnailPath
        ]);
        return $thumbnailPath;
      }

      return null;
    } catch (\Exception $e) {
      Log::error('Failed to generate thumbnail: ' . $e->getMessage());
      return null;
    }
  }

  /**
   * Upload base64 encoded image
   *
   * @param string $base64String Base64 encoded image data
   * @param string $folder Folder path within storage/app/public
   * @param string|null $filename Optional custom filename (without extension)
   * @param string $disk Storage disk to use
   * @return string|null Relative path to saved image
   */
  public function uploadBase64Image($base64String, $folder, $filename = null, $disk = 'public')
  {
    try {
      // Check if it's already a URL/path (existing image)
      if (strpos($base64String, 'data:image') !== 0) {
        return null;
      }

      // Extract image data and determine extension
      preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches);
      $extension = $matches[1] ?? 'png';

      // Validate extension
      $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
      if (!\in_array(strtolower($extension), $allowedExtensions)) {
        throw new \Exception('Invalid image type: ' . $extension);
      }

      // Remove data:image/xxx;base64, part
      $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
      $imageData = base64_decode($image);

      if ($imageData === false) {
        throw new \Exception('Failed to decode base64 image');
      }

      // Generate filename
      $generatedFilename = $filename ? $filename . '.' . $extension : Str::random(20) . '.' . $extension;
      $path = $folder . '/' . $generatedFilename;

      // Ensure directory exists
      $fullPath = Storage::disk($disk)->path($folder);
      if (!File::exists($fullPath)) {
        File::makeDirectory($fullPath, 0755, true);
      }

      // Save to storage
      $saved = Storage::disk($disk)->put($path, $imageData);

      if (!$saved) {
        throw new \Exception('Failed to save image file to: ' . $path);
      }

      Log::info('Base64 image uploaded successfully', [
        'path' => $path,
        'size' => \strlen($imageData),
        'extension' => $extension
      ]);

      return $path;
    } catch (\Exception $e) {
      Log::error('Base64 image upload failed: ' . $e->getMessage(), [
        'folder' => $folder
      ]);
      return null;
    }
  }
}
