# 🖼️ PanhaImagePreview

A modern, lightweight, zero-dependency JavaScript library for drag-and-drop image upload with built-in CRUD operations.

![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Size](https://img.shields.io/badge/size-~28KB-orange.svg)

## ✨ Features

- 🎯 **Zero Dependencies** - Pure vanilla JavaScript, no jQuery or other libraries required
- 🎨 **Beautiful UI** - Modern, responsive design with smooth animations
- 📦 **Drag & Drop** - Intuitive drag-and-drop file upload
- 🔄 **CRUD Operations** - Built-in Create, Read, Update, Delete for server integration
- 🚀 **Framework Ready** - Works seamlessly with Laravel, Vue, React, and vanilla PHP
- 📊 **Progress Tracking** - Real-time upload progress monitoring
- 🎭 **Event System** - Powerful event hooks for customization
- 📱 **Responsive** - Mobile-friendly with touch support
- 🔒 **CSRF Protection** - Auto-configure CSRF tokens for Laravel/PHP
- 🌈 **Customizable** - Theme colors and configuration options
- 📦 **Multiple Files** - Support for multiple image uploads
- ✅ **File Validation** - File type and size validation
- 🖼️ **Image Preview** - Instant preview of selected images
- 💾 **Load Existing** - Load existing images from URLs for edit mode

## 🚀 Quick Start

### Installation

Simply include the JavaScript file in your HTML:

```html
<script src="panha-image-preview-library.js"></script>
```

### Basic Usage

```html
<!DOCTYPE html>
<html>
<head>
    <title>Image Upload Demo</title>
</head>
<body>
    <div id="upload-container"></div>

    <script src="panha-image-preview-library.js"></script>
    <script>
        const uploader = new PanhaImagePreview('#upload-container', {
            maxFiles: 8,
            maxSize: 5 * 1024 * 1024, // 5MB
            onFilesChange: (files) => {
                console.log('Selected files:', files);
            }
        });
    </script>
</body>
</html>
```

## 🎯 CRUD Integration (New!)

### Laravel Example

```javascript
// Initialize
const uploader = new PanhaImagePreview('#upload-container', {
    maxFiles: 10
});

// Configure CRUD
uploader.configureCRUD({
    uploadUrl: '/api/images',
    deleteUrl: '/api/images',
    loadUrl: '/api/images',
    fieldName: 'images[]'
}).autoConfigureCSRF(); // Auto-reads from <meta name="csrf-token">

// Listen to events
uploader.on('ServerUploadSuccess', (data) => {
    console.log('Upload success:', data.response);
    alert('Images uploaded successfully!');
});

// Upload to server
document.getElementById('uploadBtn').addEventListener('click', async () => {
    try {
        const response = await uploader.uploadToServer({
            onProgress: (percent) => {
                console.log(`${percent}% complete`);
            }
        });

        console.log('Server response:', response);
        uploader.clearFiles(); // Clear after upload
    } catch (error) {
        console.error('Upload failed:', error);
    }
});

// Load existing images
await uploader.loadFromServer();
```

### Vue 3 Example

```vue
<template>
  <div>
    <div id="upload-container"></div>
    <button @click="handleUpload">Upload Images</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const uploader = ref(null);

onMounted(() => {
  uploader.value = new PanhaImagePreview('#upload-container', {
    maxFiles: 8
  });

  uploader.value
    .configureCRUD({
      uploadUrl: '/api/images',
      deleteUrl: '/api/images',
      loadUrl: '/api/images',
      fieldName: 'images[]'
    })
    .autoConfigureCSRF();

  uploader.value.on('ServerUploadSuccess', (data) => {
    console.log('Upload success:', data);
  });
});

const handleUpload = async () => {
  try {
    await uploader.value.uploadToServer();
    console.log('Upload complete!');
  } catch (error) {
    console.error('Upload failed:', error);
  }
};
</script>
```

### React Example

```jsx
import { useEffect, useRef } from 'react';

function ImageUploader() {
  const uploaderRef = useRef(null);

  useEffect(() => {
    const uploader = new window.PanhaImagePreview('#upload-container', {
      maxFiles: 8
    });

    uploader
      .configureCRUD({
        uploadUrl: '/api/images',
        deleteUrl: '/api/images',
        loadUrl: '/api/images',
        fieldName: 'images[]'
      })
      .autoConfigureCSRF();

    uploader.on('ServerUploadSuccess', (data) => {
      console.log('Upload success:', data);
    });

    uploaderRef.current = uploader;

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

## 📖 Configuration Options

```javascript
new PanhaImagePreview('#container', {
  maxFiles: 8,                    // Maximum number of files
  maxSize: 5 * 1024 * 1024,      // Max file size in bytes (5MB)
  acceptTypes: 'image/*',         // File input accept attribute
  onFilesChange: (files) => {},   // Callback when files change
  onError: (message) => {},       // Custom error handler
  theme: {                        // Custom theme colors
    primaryColor: '#7c3aed',
    gradientStart: '#667eea',
    gradientEnd: '#764ba2',
    deleteColor: '#ff4757'
  }
});
```

## 🔧 API Methods

### File Management
- `getFiles()` - Get all selected files
- `getFirstFile()` - Get first file
- `getFileByIndex(index)` - Get specific file
- `getFileCount()` - Get number of files
- `clearFiles()` - Remove all files
- `removeFileByIndex(index)` - Remove specific file
- `isEmpty()` - Check if no files selected
- `isFull()` - Check if at max capacity

### CRUD Operations
- `configureCRUD(options)` - Configure CRUD settings
- `uploadToServer(options)` - Upload files to server
- `deleteFromServer(filename, options)` - Delete file from server
- `updateOnServer(fileId, data, options)` - Update file metadata
- `loadFromServer(options)` - Load images from server
- `autoConfigureCSRF(metaName)` - Auto-configure CSRF token

### Events
- `on(eventName, callback)` - Bind event listener
- `off(eventName, callback)` - Remove event listener

### Framework Adapters
- `toLaravel()` - Export data for Laravel
- `toVue()` - Export data for Vue.js
- `toReact()` - Export data for React
- `toPHP(fieldName)` - Get FormData for PHP

### Utilities
- `validateFile(file)` - Validate a file
- `getTotalSize()` - Get total size in bytes
- `getTotalSizeFormatted()` - Get formatted size string
- `sortByName(ascending)` - Sort files by name
- `sortBySize(ascending)` - Sort files by size

## 🎪 Events

Available events for the event system:

- `CRUDConfigured` - CRUD configuration completed
- `BeforeServerUpload` - Before upload starts
- `ServerUploadSuccess` - Upload succeeded
- `ServerUploadError` - Upload failed
- `BeforeServerDelete` - Before delete starts
- `ServerDeleteSuccess` - Delete succeeded
- `ServerDeleteError` - Delete failed
- `BeforeServerUpdate` - Before update starts
- `ServerUpdateSuccess` - Update succeeded
- `ServerUpdateError` - Update failed
- `BeforeServerLoad` - Before load starts
- `ServerLoadSuccess` - Load succeeded
- `ServerLoadError` - Load failed

## 💻 Backend Integration

### Laravel Controller Example

```php
namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store('images', 'public');

            $image = Image::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'url' => Storage::url($path),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $uploadedImages[] = $image;
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => $uploadedImages
        ], 201);
    }

    public function index()
    {
        $images = Image::latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    }

    public function destroy(Request $request)
    {
        $filename = $request->input('filename');
        // Delete logic here

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
```

### Vanilla PHP Example

```php
<?php
// upload.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/uploads/';
    $uploadedFiles = [];

    foreach ($_FILES['images']['name'] as $key => $name) {
        $tmpName = $_FILES['images']['tmp_name'][$key];
        $size = $_FILES['images']['size'][$key];

        // Generate unique filename
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '_' . time() . '.' . $extension;
        $destination = $uploadDir . $uniqueName;

        if (move_uploaded_file($tmpName, $destination)) {
            $uploadedFiles[] = [
                'id' => uniqid(),
                'name' => $name,
                'url' => '/uploads/' . $uniqueName,
                'size' => $size
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Files uploaded successfully',
        'data' => $uploadedFiles
    ]);
}
?>
```

## 📝 Demo

Open `demo-crud.html` in your browser to see a full working demo with:
- Drag & drop upload
- Multiple file selection
- Progress tracking
- Event logging
- CRUD operations simulation
- Real-time status updates

## 📚 Documentation

For complete documentation, see `CLAUDE.md` which includes:
- Detailed architecture overview
- All API methods with examples
- Framework integration guides
- Backend API examples
- Best practices and patterns

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This library is open-source and available under the MIT License.

## 🙏 Credits

Created by Panha - A modern image upload solution for web developers.

## 📞 Support

For questions and support, please open an issue on the repository.

---

**Made with ❤️ for the web development community**
