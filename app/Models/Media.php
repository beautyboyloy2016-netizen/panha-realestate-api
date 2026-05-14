<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'file_name',
        'original_name',
        'file_path',
        'file_url',
        'folder_path',
        'mime_type',
        'file_extension',
        'file_size',
        'disk',
        'file_type',
        'type',
        'category',
        'thumbnail',
        'metadata',
        'user_id'
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'user_id' => 'integer'
    ];

    protected $appends = [
        'formatted_size',
        'full_url',
        'is_image',
        'is_video',
        'is_document',
        'icon_class',
        'folder_display_name'
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all mediables using this media
     */
    public function mediables()
    {
        return $this->hasMany(EntityMedia::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Get all products using this media
    // public function products()
    // {
    //     return $this->morphedByMany(Product::class, 'mediable', 'entity_media');
    // }

    public function scopeByType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeByExtension($query, string $extension)
    {
        return $query->where('file_extension', $extension);
    }

    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    public function scopeDocuments($query)
    {
        return $query->where('file_type', 'document');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function scopeLargestFirst($query)
    {
        return $query->orderBy('file_size', 'desc');
    }

    public function scopeSmallestFirst($query)
    {
        return $query->orderBy('file_size', 'asc');
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    public function isDocument(): bool
    {
        return $this->file_type === 'document';
    }

    public function getUrl(): string
    {
        if ($this->file_url) {
            return $this->file_url;
        }

        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function getFullPath(): string
    {
        return Storage::disk($this->disk)->path($this->file_path);
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->file_path);
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensions(): ?array
    {
        if (! $this->isImage() || ! isset($this->metadata['dimensions'])) {
            return null;
        }

        return $this->metadata['dimensions'];
    }

    public function getWidth(): ?int
    {
        $dimensions = $this->getDimensions();

        return $dimensions ? $dimensions['width'] : null;
    }

    public function getHeight(): ?int
    {
        $dimensions = $this->getDimensions();

        return $dimensions ? $dimensions['height'] : null;
    }

    public function hasMetadata(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
    }

    public function delete(): bool
    {
        // Delete the physical file
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->file_path);
        }

        // Delete the database record
        return parent::delete();
    }

    public static function createFromUpload($file, string $folder = 'uploads', ?int $userId = null): self
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        $fileName = uniqid() . '.' . $extension;
        $filePath = $folder . '/' . $fileName;

        $disk = config('filesystems.default', 'local');
        $file->storeAs($folder, $fileName, $disk);

        $fileType = static::getFileType($mimeType);
        $fileUrl = Storage::disk($disk)->url($filePath);

        $metadata = [];

        // Add image dimensions if it's an image
        if ($fileType === 'image') {
            $dimensions = getimagesize($file->getPathname());
            if ($dimensions) {
                $metadata['dimensions'] = [
                    'width' => $dimensions[0],
                    'height' => $dimensions[1],
                ];
            }
        }

        return static::create([
            'user_id' => $userId,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_url' => $fileUrl,
            'folder_path' => $folder,
            'mime_type' => $mimeType,
            'file_extension' => $extension,
            'file_size' => $size,
            'disk' => $disk,
            'file_type' => $fileType,
            'metadata' => $metadata,
        ]);
    }

    protected static function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'document';
    }

    /**
     * Accessor for formatted file size
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes === 0) {
            return '0 Bytes';
        }

        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Accessor for full URL (fallback if file_url is empty)
     */
    public function getFullUrlAttribute()
    {
        // Resolve application base URL from config or environment
        $appUrl = rtrim(config('app.url', env('APP_URL', url('/'))), '/');
        $configParts = parse_url($appUrl) ?: [];

        // Force HTTPS if APP_FORCE_HTTPS is enabled or if the request is secure
        $forceHttps = config('app.force_https', false) || (request() && request()->secure());

        // Apply HTTPS to appUrl if needed
        if ($forceHttps && str_starts_with($appUrl, 'http://')) {
            $appUrl = 'https://' . substr($appUrl, 7);
            $configParts = parse_url($appUrl) ?: [];
        }

        // Helper to rebuild a URL using the app's scheme/host/port but preserve path/query/fragment
        $rebuildWithAppHost = function (string $originalUrl) use ($configParts, $appUrl, $forceHttps) {
            $parts = parse_url($originalUrl) ?: [];

            // If no path, use '/' to avoid empty result
            $path = $parts['path'] ?? '/';
            $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
            $fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';

            $scheme = $forceHttps ? 'https' : ($configParts['scheme'] ?? (parse_url($appUrl, PHP_URL_SCHEME) ?: 'http'));
            $host = $configParts['host'] ?? (parse_url($appUrl, PHP_URL_HOST) ?: 'localhost');
            $port = isset($configParts['port']) ? (':' . $configParts['port']) : '';

            return $scheme . '://' . $host . $port . $path . $query . $fragment;
        };

        // If file_url is set, prefer it
        if ($this->file_url) {
            // Absolute URL
            if (filter_var($this->file_url, FILTER_VALIDATE_URL)) {
                $urlParts = parse_url($this->file_url) ?: [];

                // If host or port differ from app url, rebuild using app host/port
                $hostMismatch = isset($configParts['host']) && isset($urlParts['host']) && $configParts['host'] !== $urlParts['host'];
                $portMismatch = isset($configParts['port']) && (! isset($urlParts['port']) || $configParts['port'] != $urlParts['port']);
                $schemeMismatch = $forceHttps && isset($urlParts['scheme']) && $urlParts['scheme'] === 'http';

                if ($hostMismatch || $portMismatch || $schemeMismatch) {
                    return $rebuildWithAppHost($this->file_url);
                }

                return $this->file_url;
            }

            // Relative URL starting with '/'
            if (str_starts_with($this->file_url, '/')) {
                return $appUrl . $this->file_url;
            }

            // Other relative path
            return $appUrl . '/' . $this->file_url;
        }

        // Otherwise generate it from storage
        try {
            $disk = $this->disk ?? config('filesystems.default', 'public');
            $url = Storage::disk($disk)->url($this->file_path);

            // If storage returned a relative URL, make it absolute
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                return $appUrl . (str_starts_with($url, '/') ? '' : '/') . $url;
            }

            $urlParts = parse_url($url) ?: [];

            // If host or port differ, rebuild using app host/port
            $hostMismatch = isset($configParts['host']) && isset($urlParts['host']) && $configParts['host'] !== $urlParts['host'];
            $portMismatch = isset($configParts['port']) && (! isset($urlParts['port']) || $configParts['port'] != $urlParts['port']);
            $schemeMismatch = $forceHttps && isset($urlParts['scheme']) && $urlParts['scheme'] === 'http';

            if ($hostMismatch || $portMismatch || $schemeMismatch) {
                return $rebuildWithAppHost($url);
            }

            return $url;
        } catch (\Exception $e) {
            // Fallback to a basic URL construction using APP_URL
            return $appUrl . '/storage/' . ltrim($this->file_path, '/');
        }
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute()
    {
        return in_array($this->file_type, ['image']);
    }

    /**
     * Check if file is a video
     */
    public function getIsVideoAttribute()
    {
        return in_array($this->file_type, ['video']);
    }

    /**
     * Check if file is a document
     */
    public function getIsDocumentAttribute()
    {
        return in_array($this->file_extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
    }

    /**
     * Get file icon based on type
     */
    public function getIconClassAttribute()
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf text-danger',
            'doc' => 'fas fa-file-word text-primary',
            'docx' => 'fas fa-file-word text-primary',
            'xls' => 'fas fa-file-excel text-success',
            'xlsx' => 'fas fa-file-excel text-success',
            'ppt' => 'fas fa-file-powerpoint text-warning',
            'pptx' => 'fas fa-file-powerpoint text-warning',
            'zip' => 'fas fa-file-archive text-warning',
            'rar' => 'fas fa-file-archive text-warning',
            'mp4' => 'fas fa-file-video text-info',
            'avi' => 'fas fa-file-video text-info',
            'mp3' => 'fas fa-file-audio text-info',
            'wav' => 'fas fa-file-audio text-info',
        ];

        if ($this->file_type === 'image') {
            return 'fas fa-file-image text-primary';
        }

        return $icons[$this->file_extension] ?? 'fas fa-file text-secondary';
    }

    /**
     * Get URL for the media file (for HasMedia trait compatibility)
     */
    public function getUrlAttribute()
    {
        // Prefer the new 'path' column if it exists and is set
        if ($this->path) {
            return Storage::disk($this->disk ?? 'public')->url($this->path);
        }

        // Fall back to file_url or file_path
        return $this->full_url;
    }

    /**
     * Get thumbnail URL (for HasMedia trait compatibility)
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return Storage::disk($this->disk ?? 'public')->url($this->thumbnail);
        }

        // For images without thumbnails, return the main image
        if ($this->is_image) {
            return $this->url;
        }

        return null;
    }

    /**
     * Scope a query to only include files from a specific folder
     */
    public function scopeInFolder($query, $folderPath)
    {
        if ($folderPath) {
            return $query->where('folder_path', $folderPath);
        }

        return $query->whereNull('folder_path')->orWhere('folder_path', '');
    }

    /**
     * Scope a query to only include files by a specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include files of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Move media file to a different folder
     */
    public function moveToFolder($destinationFolder = null)
    {
        $destinationFolder = $destinationFolder ?: '';
        $oldPath = $this->file_path;
        $fileName = basename($oldPath);

        // Build new path
        $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $fileName;

        // Check if destination already exists
        if (Storage::disk($this->disk)->exists($newPath)) {
            throw new \Exception('A file with this name already exists in the destination folder');
        }

        // Move the physical file
        Storage::disk($this->disk)->move($oldPath, $newPath);

        // Update database
        $this->folder_path = $destinationFolder;
        $this->file_path = $newPath;

        // Update file URL
        $newUrl = Storage::disk($this->disk)->url($newPath);
        if (! filter_var($newUrl, FILTER_VALIDATE_URL)) {
            $newUrl = config('app.url') . (str_starts_with($newUrl, '/') ? '' : '/') . $newUrl;
        }
        $this->file_url = $newUrl;

        return $this->save();
    }

    /**
     * Copy media file to a different folder
     */
    public function copyToFolder($destinationFolder = null)
    {
        $destinationFolder = $destinationFolder ?: '';

        // Generate unique filename for copy
        $extension = $this->file_extension;
        $newFileName = \Illuminate\Support\Str::random(20) . '_' . time() . '.' . $extension;
        $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $newFileName;

        // Copy the physical file
        Storage::disk($this->disk)->copy($this->file_path, $newPath);

        // Generate new URL
        $newUrl = Storage::disk($this->disk)->url($newPath);
        if (! filter_var($newUrl, FILTER_VALIDATE_URL)) {
            $newUrl = config('app.url') . (str_starts_with($newUrl, '/') ? '' : '/') . $newUrl;
        }

        // Create new database record
        $newMedia = $this->replicate();
        $newMedia->file_name = $newFileName;
        $newMedia->file_path = $newPath;
        $newMedia->file_url = $newUrl;
        $newMedia->folder_path = $destinationFolder;
        $newMedia->user_id = auth()->id();
        $newMedia->created_at = now();
        $newMedia->updated_at = now();

        // Update metadata
        $metadata = $this->metadata ?: [];
        $metadata['copied_from'] = $this->id;
        $metadata['copied_at'] = now()->toDateTimeString();
        $metadata['copied_by'] = auth()->user() ? auth()->user()->name : 'Unknown';
        $newMedia->metadata = $metadata;

        $newMedia->save();

        return $newMedia;
    }

    /**
     * Check if file can be moved to destination folder
     */
    public function canMoveToFolder($destinationFolder = null)
    {
        $destinationFolder = $destinationFolder ?: '';
        $fileName = basename($this->file_path);
        $newPath = 'media/' . ($destinationFolder ? $destinationFolder . '/' : '') . $fileName;

        return ! Storage::disk($this->disk)->exists($newPath);
    }

    /**
     * Get folder display name
     */
    public function getFolderDisplayNameAttribute()
    {
        return $this->folder_path ?: 'Root';
    }

    /**
     * Delete the file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            // Delete the actual file from storage
            if ($media->disk && $media->file_path) {
                Storage::disk($media->disk)->delete($media->file_path);
            }
        });
    }
}
