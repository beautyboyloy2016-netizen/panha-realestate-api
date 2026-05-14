# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**PanhaImagePreview** is a standalone vanilla JavaScript library for drag-and-drop image upload and preview functionality. It has zero dependencies and is designed to be plug-and-play.

## Architecture

### Single-File Library
- **File**: `panha-image-preview-library.js`
- **Class**: `PanhaImagePreview` (exported globally and as CommonJS module)
- **Pattern**: Self-contained class with built-in styling via dynamic `<style>` injection

### Component Structure
The library injects two main UI states:
1. **Drop Zone** - Initial upload area (visible when no files selected)
2. **Gallery View** - Grid of image previews with "Add More" button (visible after upload)

### State Management
- `this.selectedFiles` - Array of File objects (max capacity controlled by `config.maxFiles`)
- `this.config` - Configuration object with validation rules and callbacks
- UI automatically switches between drop zone and gallery views based on file count

### DOM Architecture
DOM elements are dynamically generated and cached:
- `this.dropZone` - Main drop zone area
- `this.fileInput` - Hidden file input element
- `this.galleryContainer` - Gallery wrapper
- `this.imageGrid` - Grid container for image previews
- `this.addMoreBox` - "Add More" button (hidden when maxFiles reached or only 1 image)

## Key Features

### File Handling
- Drag-and-drop support with visual feedback (dragover class)
- Click-to-browse file selection
- File validation (type: image/*, size limit)
- Maximum file count enforcement
- FileReader API for local preview generation

### Image Loading
- `loadExistingImages(urls)` - Fetch remote images and convert to File objects (useful for edit mode)
- Uses Fetch API + Blob conversion
- Maintains file count limits when loading existing images

### Upload Methods
- `uploadFiles(url, options)` - Built-in XMLHttpRequest uploader with progress tracking
- `getFormData(fieldName, additionalData)` - Manual FormData generation for custom uploads
- `getFilesAsBase64()` - Convert files to base64 for JSON payloads

### UI Behavior
- Images render in grid layout (135px × 135px, responsive)
- Delete button (×) on each image (positioned top-right, z-index: 10)
- "Add More" button appears only when: `selectedFiles.length >= 2 && selectedFiles.length < maxFiles`
- Animations: fade-in on image add, hover effects on delete button

## Configuration Options

```javascript
new PanhaImagePreview('#container', {
  maxFiles: 8,                    // Maximum number of files
  maxSize: 5 * 1024 * 1024,      // Max file size in bytes (default: 5MB)
  acceptTypes: 'image/*',         // File input accept attribute
  onFilesChange: (files) => {},   // Callback when files array changes
  onError: (message) => {},       // Custom error handler (default: alert)
  theme: {                        // Color customization
    primaryColor: '#7c3aed',
    gradientStart: '#667eea',
    gradientEnd: '#764ba2',
    deleteColor: '#ff4757'
  }
});
```

## CRUD Operations (New Feature)

The library now includes built-in CRUD operations for seamless server integration.

### Configure CRUD
```javascript
uploader.configureCRUD({
  uploadUrl: '/api/images',
  deleteUrl: '/api/images/:id',
  updateUrl: '/api/images/:id',
  loadUrl: '/api/images',
  fieldName: 'images[]',
  csrfToken: 'your-csrf-token', // or use autoConfigureCSRF()
  additionalData: {
    user_id: 123,
    album: 'profile'
  }
});

// Or auto-configure CSRF from meta tag
uploader.autoConfigureCSRF(); // Reads from <meta name="csrf-token">
```

### CRUD Methods
- `uploadToServer(options)` - Upload all selected files to server
- `deleteFromServer(filename, options)` - Delete file from server by filename
- `updateOnServer(fileId, updateData, options)` - Update file metadata
- `loadFromServer(options)` - Load existing images from server
- `getCSRFToken(metaName)` - Get CSRF token from meta tag
- `autoConfigureCSRF(metaName)` - Auto-configure CSRF from meta tag

### Event System
- `on(eventName, callback)` - Bind event listener
- `off(eventName, callback)` - Remove event listener
- `trigger(eventName, data)` - Trigger event (internal use)

### Available Events
- `CRUDConfigured` - Fired when CRUD is configured
- `BeforeServerUpload` - Before upload starts
- `ServerUploadSuccess` - Upload completed successfully
- `ServerUploadError` - Upload failed
- `BeforeServerDelete` - Before delete starts
- `ServerDeleteSuccess` - Delete completed successfully
- `ServerDeleteError` - Delete failed
- `BeforeServerUpdate` - Before update starts
- `ServerUpdateSuccess` - Update completed successfully
- `ServerUpdateError` - Update failed
- `BeforeServerLoad` - Before load starts
- `ServerLoadSuccess` - Load completed successfully
- `ServerLoadError` - Load failed

### Framework Adapters
- `toLaravel()` - Get data formatted for Laravel
- `toVue()` - Get data formatted for Vue.js
- `toReact()` - Get data formatted for React
- `toPHP(fieldName)` - Get FormData for PHP upload

## Public API Methods

### File Access
- `getFiles()` - Returns full array of File objects
- `getFirstFile()` - Returns first file or null
- `getFileByIndex(index)` - Get specific file
- `getFileCount()` - Returns number of files

### File Manipulation
- `clearFiles()` - Remove all files and reset to drop zone view
- `removeFileByIndex(index)` - Remove specific file and re-render
- `sortFiles(compareFn)` - Custom sort with re-render
- `sortByName(ascending)`, `sortBySize(ascending)`, `sortByType(ascending)` - Convenience sorters

### File Operations
- `filterFiles(callback)` - Filter files by criteria
- `findFile(callback)` - Find single file
- `validateFile(file)` - Validate a file before adding (returns `{valid, error}`)

### State Queries
- `isEmpty()` - Check if no files selected
- `isFull()` - Check if at max capacity
- `getRemainingSlots()` - Get available slots
- `getTotalSize()` - Total size in bytes
- `getTotalSizeFormatted()` - Human-readable size string

### UI Control
- `disableBrowse(disable)` / `enableBrowse()` - Toggle file input functionality
- `refresh()` - Re-render current state
- `destroy()` - Clean up event listeners and DOM

### Dynamic Configuration
- `setOption(option, value)` - Update single option
- `setOption({multiple: 'options'})` - Update multiple options
- `getOption(option)` - Get option value

## Important Implementation Details

### Delete Button Event Handling
The delete button uses `addEventListener` instead of `onclick` to properly handle events:
```javascript
deleteBtn.type = 'button'; // CRITICAL: Prevent form submission
deleteBtn.addEventListener('click', (e) => {
  e.preventDefault();
  e.stopPropagation();
  this.removeImage(index);
});
```

### Style Injection
Styles are injected once via `injectStyles()`. Check prevents duplicate injection:
```javascript
if (document.getElementById('image-preview-upload-styles')) return;
```

### File Input Reset
After file selection, input value is cleared to allow re-selection of same files:
```javascript
e.target.value = ''; // Reset input
```

### Global Drag Prevention
Document-level event listeners prevent default drag behaviors:
```javascript
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  document.addEventListener(eventName, this.preventDefaults, false);
});
```

## Responsive Breakpoints

- **Desktop**: 135px × 135px image items
- **Tablet** (≤768px): 120px × 120px, centered grid
- **Mobile** (≤480px): 100px × 100px, smaller upload icon (60px)

## Module Exports

```javascript
// CommonJS (Node.js)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = PanhaImagePreview;
}

// Browser global
if (typeof window !== 'undefined') {
  window.PanhaImagePreview = PanhaImagePreview;
}
```

## Backend API CRUD Examples

### PHP (Vanilla)

```php
<?php
// api/images.php - Complete CRUD API endpoint

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$uploadDir = __DIR__ . '/uploads/';

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

switch ($method) {
    case 'POST': // Create/Upload
        handleUpload();
        break;
    case 'GET': // Read
        handleGet();
        break;
    case 'DELETE': // Delete
        handleDelete();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleUpload() {
    global $uploadDir;

    try {
        if (!isset($_FILES['images'])) {
            throw new Exception('No files uploaded');
        }

        $files = $_FILES['images'];
        $uploadedFiles = [];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Handle multiple files
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $fileTmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $fileType = is_array($files['type']) ? $files['type'][$i] : $files['type'];

            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                throw new Exception("Invalid file type: {$fileName}");
            }

            // Validate file size
            if ($fileSize > $maxSize) {
                throw new Exception("File too large: {$fileName}");
            }

            // Generate unique filename
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueName = uniqid() . '_' . time() . '.' . $extension;
            $destination = $uploadDir . $uniqueName;

            // Move uploaded file
            if (move_uploaded_file($fileTmp, $destination)) {
                $uploadedFiles[] = [
                    'id' => uniqid(),
                    'name' => $fileName,
                    'path' => $uniqueName,
                    'url' => '/uploads/' . $uniqueName,
                    'size' => $fileSize,
                    'type' => $fileType,
                    'uploaded_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Files uploaded successfully',
            'data' => $uploadedFiles
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleGet() {
    global $uploadDir;

    try {
        $images = [];
        $files = glob($uploadDir . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $images[] = [
                    'id' => md5($filename),
                    'name' => $filename,
                    'url' => '/uploads/' . $filename,
                    'size' => filesize($file),
                    'uploaded_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $images
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleDelete() {
    global $uploadDir;

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['filename'])) {
            throw new Exception('Filename required');
        }

        $filename = basename($data['filename']); // Prevent directory traversal
        $filepath = $uploadDir . $filename;

        if (!file_exists($filepath)) {
            throw new Exception('File not found');
        }

        if (unlink($filepath)) {
            echo json_encode([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete file');
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
```

### Laravel

#### Migration
```php
// database/migrations/xxxx_create_images_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path');
            $table->string('url');
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};
```

#### Model
```php
// app/Models/Image.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'url',
        'size',
        'mime_type',
        'user_id',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected $appends = ['full_url'];

    public function getFullUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::deleting(function ($image) {
            Storage::delete($image->path);
        });
    }
}
```

#### Controller
```php
// app/Http/Controllers/ImageController.php
<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    /**
     * Display a listing of images
     */
    public function index(Request $request)
    {
        $query = Image::query();

        if ($request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $images = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }

    /**
     * Store newly uploaded images
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            // Store file in storage/app/public/images
            $path = $file->store('images', 'public');

            // Create database record
            $image = Image::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'url' => Storage::url($path),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'user_id' => $request->user()->id ?? null,
            ]);

            $uploadedImages[] = $image;
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => $uploadedImages
        ], 201);
    }

    /**
     * Display the specified image
     */
    public function show(Image $image)
    {
        return response()->json([
            'success' => true,
            'data' => $image
        ]);
    }

    /**
     * Update the specified image metadata
     */
    public function update(Request $request, Image $image)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $image->update($request->only(['name']));

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'data' => $image
        ]);
    }

    /**
     * Remove the specified image
     */
    public function destroy(Image $image)
    {
        $image->delete(); // Will trigger model event to delete file

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    /**
     * Bulk delete images
     */
    public function bulkDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'required|exists:images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $count = Image::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} images deleted successfully"
        ]);
    }
}
```

#### Routes
```php
// routes/api.php
use App\Http\Controllers\ImageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('images', ImageController::class);
    Route::post('images/bulk-delete', [ImageController::class, 'bulkDestroy']);
});
```

## Frontend Integration Examples

### Vue 3 (Composition API)

```vue
<!-- components/ImageUploader.vue -->
<template>
  <div class="image-uploader">
    <div id="upload-container"></div>

    <div v-if="uploading" class="upload-progress">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
      </div>
      <p>{{ uploadProgress }}% uploaded</p>
    </div>

    <div class="actions">
      <button @click="handleUpload" :disabled="uploading || uploader?.isEmpty()">
        {{ uploading ? 'Uploading...' : 'Upload Images' }}
      </button>
      <button @click="loadExisting" v-if="existingImages.length">
        Load Existing
      </button>
    </div>

    <!-- Display saved images -->
    <div v-if="savedImages.length" class="saved-images">
      <h3>Saved Images</h3>
      <div class="image-list">
        <div v-for="image in savedImages" :key="image.id" class="image-card">
          <img :src="image.url" :alt="image.name" />
          <p>{{ image.name }}</p>
          <button @click="deleteImage(image.id)" class="delete-btn">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

const props = defineProps({
  maxFiles: { type: Number, default: 8 },
  apiUrl: { type: String, default: '/api/images' },
  existingImages: { type: Array, default: () => [] }
});

const emit = defineEmits(['uploaded', 'deleted']);

const uploader = ref(null);
const uploading = ref(false);
const uploadProgress = ref(0);
const savedImages = ref([]);

onMounted(() => {
  // Initialize PanhaImagePreview
  uploader.value = new PanhaImagePreview('#upload-container', {
    maxFiles: props.maxFiles,
    maxSize: 5 * 1024 * 1024,
    onFilesChange: (files) => {
      console.log('Files changed:', files.length);
    },
    onError: (message) => {
      alert(message);
    }
  });

  // Fetch existing images
  fetchImages();
});

onBeforeUnmount(() => {
  if (uploader.value) {
    uploader.value.destroy();
  }
});

const fetchImages = async () => {
  try {
    const response = await axios.get(props.apiUrl);
    savedImages.value = response.data.data.data || response.data.data;
  } catch (error) {
    console.error('Error fetching images:', error);
  }
};

const handleUpload = async () => {
  if (uploader.value.isEmpty()) {
    alert('Please select at least one image');
    return;
  }

  uploading.value = true;
  uploadProgress.value = 0;

  try {
    // Get CSRF token (for Laravel)
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const response = await uploader.value.uploadFiles(props.apiUrl, {
      fieldName: 'images[]',
      headers: token ? { 'X-CSRF-TOKEN': token } : {},
      onProgress: (percent) => {
        uploadProgress.value = Math.round(percent);
      }
    });

    // Clear uploader
    uploader.value.clearFiles();

    // Refresh saved images
    await fetchImages();

    emit('uploaded', response.data);
    alert('Images uploaded successfully!');

  } catch (error) {
    console.error('Upload error:', error);
    alert('Failed to upload images');
  } finally {
    uploading.value = false;
    uploadProgress.value = 0;
  }
};

const deleteImage = async (id) => {
  if (!confirm('Are you sure you want to delete this image?')) return;

  try {
    await axios.delete(`${props.apiUrl}/${id}`);
    savedImages.value = savedImages.value.filter(img => img.id !== id);
    emit('deleted', id);
    alert('Image deleted successfully');
  } catch (error) {
    console.error('Delete error:', error);
    alert('Failed to delete image');
  }
};

const loadExisting = async () => {
  if (!props.existingImages.length) return;

  try {
    const urls = props.existingImages.map(img => img.url);
    await uploader.value.loadExistingImages(urls);
  } catch (error) {
    console.error('Error loading existing images:', error);
  }
};
</script>

<style scoped>
.image-uploader {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.upload-progress {
  margin: 20px 0;
}

.progress-bar {
  width: 100%;
  height: 30px;
  background: #f0f0f0;
  border-radius: 15px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s ease;
}

.actions {
  display: flex;
  gap: 10px;
  margin: 20px 0;
}

.actions button {
  padding: 12px 24px;
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
}

.actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.saved-images {
  margin-top: 40px;
}

.image-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.image-card {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 10px;
  text-align: center;
}

.image-card img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
}

.image-card p {
  margin: 10px 0;
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.delete-btn {
  background: #ff4757;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
}

.delete-btn:hover {
  background: #ff3838;
}
</style>
```

#### Usage in Vue App
```vue
<!-- App.vue or parent component -->
<template>
  <div id="app">
    <h1>Image Gallery</h1>
    <ImageUploader
      :max-files="10"
      api-url="/api/images"
      :existing-images="existingImages"
      @uploaded="handleUploaded"
      @deleted="handleDeleted"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ImageUploader from './components/ImageUploader.vue';

const existingImages = ref([]);

const handleUploaded = (data) => {
  console.log('Uploaded:', data);
};

const handleDeleted = (id) => {
  console.log('Deleted:', id);
};
</script>
```

### React

```jsx
// components/ImageUploader.jsx
import React, { useEffect, useRef, useState } from 'react';
import axios from 'axios';
import './ImageUploader.css';

const ImageUploader = ({
  maxFiles = 8,
  apiUrl = '/api/images',
  existingImages = [],
  onUploaded,
  onDeleted
}) => {
  const uploaderRef = useRef(null);
  const [uploader, setUploader] = useState(null);
  const [uploading, setUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(0);
  const [savedImages, setSavedImages] = useState([]);

  useEffect(() => {
    // Initialize PanhaImagePreview
    const uploaderInstance = new window.PanhaImagePreview('#upload-container', {
      maxFiles,
      maxSize: 5 * 1024 * 1024,
      onFilesChange: (files) => {
        console.log('Files changed:', files.length);
      },
      onError: (message) => {
        alert(message);
      }
    });

    setUploader(uploaderInstance);
    fetchImages();

    // Cleanup
    return () => {
      if (uploaderInstance) {
        uploaderInstance.destroy();
      }
    };
  }, [maxFiles]);

  const fetchImages = async () => {
    try {
      const response = await axios.get(apiUrl);
      setSavedImages(response.data.data.data || response.data.data);
    } catch (error) {
      console.error('Error fetching images:', error);
    }
  };

  const handleUpload = async () => {
    if (!uploader || uploader.isEmpty()) {
      alert('Please select at least one image');
      return;
    }

    setUploading(true);
    setUploadProgress(0);

    try {
      // Get CSRF token (for Laravel)
      const token = document.querySelector('meta[name="csrf-token"]')?.content;

      const response = await uploader.uploadFiles(apiUrl, {
        fieldName: 'images[]',
        headers: token ? { 'X-CSRF-TOKEN': token } : {},
        onProgress: (percent) => {
          setUploadProgress(Math.round(percent));
        }
      });

      // Clear uploader
      uploader.clearFiles();

      // Refresh saved images
      await fetchImages();

      if (onUploaded) onUploaded(response.data);
      alert('Images uploaded successfully!');

    } catch (error) {
      console.error('Upload error:', error);
      alert('Failed to upload images');
    } finally {
      setUploading(false);
      setUploadProgress(0);
    }
  };

  const deleteImage = async (id) => {
    if (!window.confirm('Are you sure you want to delete this image?')) return;

    try {
      await axios.delete(`${apiUrl}/${id}`);
      setSavedImages(savedImages.filter(img => img.id !== id));
      if (onDeleted) onDeleted(id);
      alert('Image deleted successfully');
    } catch (error) {
      console.error('Delete error:', error);
      alert('Failed to delete image');
    }
  };

  const loadExisting = async () => {
    if (!existingImages.length || !uploader) return;

    try {
      const urls = existingImages.map(img => img.url);
      await uploader.loadExistingImages(urls);
    } catch (error) {
      console.error('Error loading existing images:', error);
    }
  };

  return (
    <div className="image-uploader">
      <div id="upload-container"></div>

      {uploading && (
        <div className="upload-progress">
          <div className="progress-bar">
            <div
              className="progress-fill"
              style={{ width: `${uploadProgress}%` }}
            ></div>
          </div>
          <p>{uploadProgress}% uploaded</p>
        </div>
      )}

      <div className="actions">
        <button
          onClick={handleUpload}
          disabled={uploading || (uploader && uploader.isEmpty())}
        >
          {uploading ? 'Uploading...' : 'Upload Images'}
        </button>
        {existingImages.length > 0 && (
          <button onClick={loadExisting}>
            Load Existing
          </button>
        )}
      </div>

      {savedImages.length > 0 && (
        <div className="saved-images">
          <h3>Saved Images</h3>
          <div className="image-list">
            {savedImages.map(image => (
              <div key={image.id} className="image-card">
                <img src={image.url} alt={image.name} />
                <p>{image.name}</p>
                <button
                  onClick={() => deleteImage(image.id)}
                  className="delete-btn"
                >
                  Delete
                </button>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default ImageUploader;
```

#### CSS for React Component
```css
/* components/ImageUploader.css */
.image-uploader {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.upload-progress {
  margin: 20px 0;
}

.progress-bar {
  width: 100%;
  height: 30px;
  background: #f0f0f0;
  border-radius: 15px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  transition: width 0.3s ease;
}

.actions {
  display: flex;
  gap: 10px;
  margin: 20px 0;
}

.actions button {
  padding: 12px 24px;
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
}

.actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.saved-images {
  margin-top: 40px;
}

.image-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.image-card {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  padding: 10px;
  text-align: center;
}

.image-card img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 8px;
}

.image-card p {
  margin: 10px 0;
  font-size: 14px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.delete-btn {
  background: #ff4757;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
}

.delete-btn:hover {
  background: #ff3838;
}
```

#### Usage in React App
```jsx
// App.jsx
import React, { useState } from 'react';
import ImageUploader from './components/ImageUploader';

function App() {
  const [existingImages] = useState([]);

  const handleUploaded = (data) => {
    console.log('Uploaded:', data);
  };

  const handleDeleted = (id) => {
    console.log('Deleted:', id);
  };

  return (
    <div className="App">
      <h1>Image Gallery</h1>
      <ImageUploader
        maxFiles={10}
        apiUrl="/api/images"
        existingImages={existingImages}
        onUploaded={handleUploaded}
        onDeleted={handleDeleted}
      />
    </div>
  );
}

export default App;
```

## Testing Considerations

When testing or debugging:
1. Check that container element exists before initialization (constructor throws error if not found)
2. Verify `onFilesChange` callback receives updated files array after all operations
3. Test file validation edge cases (oversized files, non-image files, exceeding maxFiles)
4. Ensure delete button `e.stopPropagation()` prevents triggering parent click events
5. Test `loadExistingImages()` with CORS-enabled URLs (requires appropriate headers)
6. Verify XHR upload progress tracking works with large files
7. Test CSRF token handling in Laravel integration
8. Verify image storage permissions (PHP/Laravel upload directories must be writable)
9. Test bulk delete operations with multiple selected images

## Common Integration Patterns

### Basic Usage
```javascript
const uploader = new PanhaImagePreview('#upload-container', {
  maxFiles: 5,
  onFilesChange: (files) => console.log('Files:', files)
});
```

### CRUD Integration (Recommended - New!)

#### Laravel with CRUD
```javascript
// Initialize uploader
const uploader = new PanhaImagePreview('#upload-container', {
  maxFiles: 10,
  maxSize: 5 * 1024 * 1024
});

// Configure CRUD with auto CSRF
uploader.configureCRUD({
  uploadUrl: '/api/images',
  deleteUrl: '/api/images',
  loadUrl: '/api/images',
  fieldName: 'images[]',
  additionalData: {
    album_id: 123
  }
}).autoConfigureCSRF(); // Auto-reads from <meta name="csrf-token">

// Listen to events
uploader.on('ServerUploadSuccess', (data) => {
  console.log('Upload success:', data.response);
  alert('Images uploaded successfully!');
});

uploader.on('ServerUploadError', (data) => {
  console.error('Upload error:', data.error);
  alert('Upload failed: ' + data.error.message);
});

// Upload button handler
document.getElementById('uploadBtn').addEventListener('click', async () => {
  try {
    const response = await uploader.uploadToServer({
      onProgress: (percent) => {
        console.log(`Upload progress: ${percent}%`);
        // Update progress bar
      }
    });

    console.log('Server response:', response);
    uploader.clearFiles(); // Clear after successful upload
  } catch (error) {
    console.error('Upload failed:', error);
  }
});

// Load existing images on page load
window.addEventListener('DOMContentLoaded', async () => {
  try {
    await uploader.loadFromServer();
  } catch (error) {
    console.error('Failed to load images:', error);
  }
});
```

#### Vue 3 with CRUD
```javascript
// In your Vue component
import { ref, onMounted } from 'vue';

const uploader = ref(null);

onMounted(() => {
  uploader.value = new PanhaImagePreview('#upload-container', {
    maxFiles: 8
  });

  // Configure CRUD
  uploader.value
    .configureCRUD({
      uploadUrl: '/api/images',
      deleteUrl: '/api/images',
      loadUrl: '/api/images',
      fieldName: 'images[]'
    })
    .autoConfigureCSRF();

  // Bind Vue-friendly events
  uploader.value.on('ServerUploadSuccess', handleUploadSuccess);
  uploader.value.on('ServerUploadError', handleUploadError);

  // Load existing images
  loadImages();
});

const handleUpload = async () => {
  try {
    await uploader.value.uploadToServer();
    await loadImages(); // Refresh gallery
  } catch (error) {
    console.error('Upload failed:', error);
  }
};

const loadImages = async () => {
  try {
    await uploader.value.loadFromServer();
  } catch (error) {
    console.error('Load failed:', error);
  }
};
```

#### React with CRUD
```javascript
import { useEffect, useRef } from 'react';

function ImageUploadComponent() {
  const uploaderRef = useRef(null);

  useEffect(() => {
    const uploader = new window.PanhaImagePreview('#upload-container', {
      maxFiles: 8
    });

    // Configure CRUD
    uploader
      .configureCRUD({
        uploadUrl: '/api/images',
        deleteUrl: '/api/images',
        loadUrl: '/api/images',
        fieldName: 'images[]'
      })
      .autoConfigureCSRF();

    // Event listeners
    uploader.on('ServerUploadSuccess', (data) => {
      console.log('Upload success:', data);
    });

    uploaderRef.current = uploader;

    // Load existing images
    uploader.loadFromServer().catch(console.error);

    return () => uploader.destroy();
  }, []);

  const handleUpload = async () => {
    try {
      await uploaderRef.current.uploadToServer();
    } catch (error) {
      console.error('Upload failed:', error);
    }
  };

  return (
    <div>
      <div id="upload-container"></div>
      <button onClick={handleUpload}>Upload Images</button>
    </div>
  );
}
```

#### PHP (Vanilla) with CRUD
```javascript
// Initialize
const uploader = new PanhaImagePreview('#upload-container', {
  maxFiles: 5
});

// Configure CRUD for vanilla PHP
uploader.configureCRUD({
  uploadUrl: '/api/upload.php',
  deleteUrl: '/api/delete.php',
  loadUrl: '/api/images.php',
  fieldName: 'images[]',
  csrfToken: '<?php echo $_SESSION["csrf_token"]; ?>' // PHP-generated token
});

// Upload handler
document.getElementById('uploadForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  try {
    const response = await uploader.uploadToServer();
    alert('Upload successful!');
    uploader.clearFiles();
  } catch (error) {
    alert('Upload failed: ' + error.message);
  }
});
```

### Form Integration (Manual)
```javascript
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = uploader.getFormData('images[]', {
    user_id: 123,
    category: 'profile'
  });

  // Send formData via fetch
  const response = await fetch('/api/upload', {
    method: 'POST',
    body: formData
  });
});
```

### Edit Mode (Load Existing Images)
```javascript
// Load from URLs
await uploader.loadExistingImages([
  'https://example.com/image1.jpg',
  'https://example.com/image2.jpg'
]);

// Or use CRUD to load from server
await uploader.loadFromServer();
```

### Legacy Upload Method (Still Supported)
```javascript
uploader.uploadFiles('/api/upload', {
  fieldName: 'photos[]',
  additionalData: { album_id: 456 },
  headers: { 'X-CSRF-Token': token },
  onProgress: (percent) => console.log(`${percent}% complete`),
  onSuccess: (response) => console.log('Uploaded:', response),
  onError: (error) => console.error('Failed:', error)
});
```

### Advanced CRUD Usage

#### Delete Image from Server
```javascript
// After user clicks delete button
async function deleteImage(filename) {
  try {
    await uploader.deleteFromServer(filename);
    console.log('Image deleted from server');
  } catch (error) {
    console.error('Delete failed:', error);
  }
}
```

#### Update Image Metadata
```javascript
async function updateImageName(imageId, newName) {
  try {
    await uploader.updateOnServer(imageId, { name: newName });
    console.log('Image updated on server');
  } catch (error) {
    console.error('Update failed:', error);
  }
}
```

#### Framework Data Export
```javascript
// For Laravel
const laravelData = uploader.toLaravel();
console.log(laravelData); // { images: [...], image_count: 3, _token: '...' }

// For Vue/React
const vueData = uploader.toVue();
console.log(vueData); // { files: [...], count: 3, isEmpty: false, isFull: false }

// For PHP (FormData)
const phpFormData = uploader.toPHP('files[]');
// Ready to submit via fetch/XMLHttpRequest
```
